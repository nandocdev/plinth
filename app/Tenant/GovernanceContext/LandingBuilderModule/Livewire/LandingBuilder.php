<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Livewire;

use App\Tenant\GovernanceContext\LandingBuilderModule\Actions\ApplyTenantLandingTemplateAction;
use App\Tenant\GovernanceContext\LandingBuilderModule\Actions\GetOrCreateTenantLandingAction;
use App\Tenant\GovernanceContext\LandingBuilderModule\Actions\UpdateLandingBlockSettingsAction;
use App\Tenant\GovernanceContext\LandingBuilderModule\Actions\UpdateTenantLandingContentAction;
use App\Tenant\GovernanceContext\LandingBuilderModule\Livewire\Forms\LandingBuilderForm;
use App\Tenant\GovernanceContext\LandingBuilderModule\Models\LandingBlock;
use App\Tenant\GovernanceContext\LandingBuilderModule\Models\TenantLanding;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Landing Builder')]
final class LandingBuilder extends Component {
   public LandingBuilderForm $form;

   public ?string $message = null;

   public int $landingId = 0;

   public string $templateKey = 'corporate';

   public int $selectedBlockId = 0;

   /** @var array<string, mixed> */
   public array $editingSettings = [];

   public bool $editingBlockActive = true;

   /** @var array<int, array<string, mixed>> */
   public array $blocks = [];

   public int $previewVersion = 1;

   public function mount(GetOrCreateTenantLandingAction $action): void {
      $this->authorize('viewAny', TenantLanding::class);

      $landing = $action->execute();
      $this->landingId = $landing->id;

      $globalSettings = is_array($landing->global_settings) ? $landing->global_settings : [];

      $this->templateKey = (string) ($landing->template_key ?? 'corporate');
      $this->form->siteName = (string) ($globalSettings['site_name'] ?? 'Mi Empresa');
      $this->form->cta = (string) ($globalSettings['default_cta'] ?? 'Comenzar');
      $this->form->primaryColor = (string) ($landing->primary_color ?? '#2563eb');
      $this->form->status = (string) ($landing->status ?? 'draft');

      $this->refreshBlocks();

      if (!empty($this->blocks)) {
         $this->selectBlock((int) $this->blocks[0]['id']);
      }
   }

   public function save(UpdateTenantLandingContentAction $action): void {
      $this->authorize('update', TenantLanding::class);

      $this->form->validate();

      $landing = TenantLanding::query()->findOrFail($this->landingId);

      $updated = $action->execute($landing, $this->form->toData());

      $this->form->status = (string) $updated->status;
      $this->message = 'Landing actualizada correctamente.';

      $this->refreshBlocks();
      $this->refreshPreview();
   }

   public function publish(UpdateTenantLandingContentAction $action): void {
      $this->form->status = 'published';
      $this->save($action);
   }

   public function unpublish(UpdateTenantLandingContentAction $action): void {
      $this->form->status = 'draft';
      $this->save($action);
   }

   public function selectTemplate(string $templateKey, ApplyTenantLandingTemplateAction $action): void {
      $this->authorize('update', TenantLanding::class);

      if (!collect(TenantLanding::availableTemplates())->pluck('key')->contains($templateKey)) {
         return;
      }

      $landing = TenantLanding::query()->findOrFail($this->landingId);
      $updated = $action->execute($landing, $templateKey);

      $this->templateKey = (string) $updated->template_key;
      $this->form->primaryColor = (string) ($updated->primary_color ?? '#2563eb');

      $globalSettings = is_array($updated->global_settings) ? $updated->global_settings : [];
      $this->form->siteName = (string) ($globalSettings['site_name'] ?? $this->form->siteName);
      $this->form->cta = (string) ($globalSettings['default_cta'] ?? $this->form->cta);

      $this->refreshBlocks();

      if (!empty($this->blocks)) {
         $this->selectBlock((int) $this->blocks[0]['id']);
      }

      $this->message = 'Plantilla aplicada correctamente.';
      $this->refreshPreview();
   }

   public function selectBlock(int $blockId): void {
      $block = LandingBlock::query()->where('tenant_landing_id', $this->landingId)->findOrFail($blockId);

      $this->selectedBlockId = $block->id;
      $this->editingBlockActive = (bool) $block->is_active;
      $this->editingSettings = $this->normalizedSettings($block);
   }

   public function toggleBlock(): void {
      if ($this->selectedBlockType() === 'navbar') {
         return;
      }

      $this->editingBlockActive = ! $this->editingBlockActive;
   }

   public function addBlock(string $blockType): void {
      $this->authorize('update', TenantLanding::class);

      $allowed = collect(config('landing_templates.available_blocks', []));

      if (! $allowed->contains($blockType)) {
         return;
      }

      $maxOrder = (int) LandingBlock::query()
         ->where('tenant_landing_id', $this->landingId)
         ->max('order');

      $block = LandingBlock::query()->create([
         'tenant_landing_id' => $this->landingId,
         'block_type' => $blockType,
         'order' => $maxOrder + 1,
         'is_active' => true,
         'settings' => $this->defaultSettingsForBlock($blockType),
      ]);

      $this->refreshBlocks();
      $this->selectBlock($block->id);
      $this->refreshPreview();
      $this->message = 'Bloque agregado correctamente.';
   }

   public function removeSelectedBlock(): void {
      $this->authorize('update', TenantLanding::class);

      if ($this->selectedBlockId <= 0) {
         return;
      }

      $selectedBlock = LandingBlock::query()
         ->where('tenant_landing_id', $this->landingId)
         ->find($this->selectedBlockId);

      if ($selectedBlock instanceof LandingBlock && $selectedBlock->block_type === 'navbar') {
         $this->message = 'El bloque Header / Navbar es fijo y no puede eliminarse.';

         return;
      }

      LandingBlock::query()
         ->where('tenant_landing_id', $this->landingId)
         ->whereKey($this->selectedBlockId)
         ->delete();

      $this->resequenceBlocks();
      $this->refreshBlocks();

      $this->selectedBlockId = 0;
      $this->editingSettings = [];

      if (!empty($this->blocks)) {
         $this->selectBlock((int) $this->blocks[0]['id']);
      }

      $this->refreshPreview();
      $this->message = 'Bloque eliminado correctamente.';
   }

   public function moveSelectedBlockUp(): void {
      $this->moveSelectedBlock(-1);
   }

   public function moveSelectedBlockDown(): void {
      $this->moveSelectedBlock(1);
   }

   public function saveBlock(UpdateLandingBlockSettingsAction $action): void {
      $block = LandingBlock::query()->where('tenant_landing_id', $this->landingId)->findOrFail($this->selectedBlockId);

      $validated = Validator::make(
         ['settings' => $this->editingSettings],
         $this->rulesForBlock($block->block_type),
      )->validate();

      $action->execute($block, $validated['settings'], $block->block_type === 'navbar' ? true : $this->editingBlockActive);

      $this->message = 'Bloque actualizado correctamente.';
      $this->refreshBlocks();
      $this->selectBlock($block->id);
      $this->refreshPreview();
   }

   public function render(): View {
      $selectedBlock = collect($this->blocks)->firstWhere('id', $this->selectedBlockId);

      return view('landing-builder::livewire.landing-builder', [
         'publicUrl' => route('tenant.landing.public', ['tenantDomain' => request()->route('tenantDomain')]),
         'previewUrl' => route('tenant.landing.preview', ['tenantDomain' => request()->route('tenantDomain'), 'v' => $this->previewVersion]),
         'selectedBlockType' => (string) ($selectedBlock['block_type'] ?? ''),
         'availableTemplates' => TenantLanding::availableTemplates(),
         'availableBlocks' => config('landing_templates.available_blocks', []),
         'blockLabels' => config('landing_templates.block_labels', []),
      ]);
   }

   private function refreshPreview(): void {
      $this->previewVersion++;
   }

   private function refreshBlocks(): void {
      /** @var Collection<int, LandingBlock> $blocks */
      $blocks = LandingBlock::query()
         ->where('tenant_landing_id', $this->landingId)
         ->orderBy('order')
         ->get();

      $this->blocks = $blocks
         ->map(fn(LandingBlock $b) => [
            'id' => $b->id,
            'block_type' => $b->block_type,
            'is_active' => (bool) $b->is_active,
            'order' => (int) $b->order,
         ])
         ->all();
   }

   private function moveSelectedBlock(int $direction): void {
      $this->authorize('update', TenantLanding::class);

      if ($this->selectedBlockId <= 0 || !in_array($direction, [-1, 1], true)) {
         return;
      }

      $orderedBlocks = LandingBlock::query()
         ->where('tenant_landing_id', $this->landingId)
         ->orderBy('order')
         ->orderBy('id')
         ->get();

      $selected = $orderedBlocks->firstWhere('id', $this->selectedBlockId);

      if ($selected instanceof LandingBlock && $selected->block_type === 'navbar') {
         return;
      }

      $currentIndex = $orderedBlocks->search(
         fn(LandingBlock $block): bool => $block->id === $this->selectedBlockId,
      );

      if (!is_int($currentIndex)) {
         return;
      }

      $targetIndex = $currentIndex + $direction;

      if ($targetIndex < 0 || $targetIndex >= $orderedBlocks->count()) {
         return;
      }

      $targetBlock = $orderedBlocks->values()->get($targetIndex);

      if ($targetBlock instanceof LandingBlock && $targetBlock->block_type === 'navbar') {
         return;
      }

      $items = $orderedBlocks->values()->all();
      $tmp = $items[$currentIndex];
      $items[$currentIndex] = $items[$targetIndex];
      $items[$targetIndex] = $tmp;

      DB::transaction(function () use ($items): void {
         foreach ($items as $index => $block) {
            LandingBlock::query()->whereKey($block->id)->update(['order' => $index + 1]);
         }
      });

      $this->refreshBlocks();
      $this->selectBlock($this->selectedBlockId);
      $this->refreshPreview();
      $this->message = 'Orden de bloques actualizado.';
   }

   private function resequenceBlocks(): void {
      $blocks = LandingBlock::query()
         ->where('tenant_landing_id', $this->landingId)
         ->orderBy('order')
         ->orderBy('id')
         ->get();

      DB::transaction(function () use ($blocks): void {
         $order = 1;

         foreach ($blocks as $block) {
            if ($block->block_type === 'navbar') {
               LandingBlock::query()->whereKey($block->id)->update(['order' => 1, 'is_active' => true]);

               continue;
            }

            $order = max($order, 2);
            LandingBlock::query()->whereKey($block->id)->update(['order' => $order]);
            $order++;
         }
      });
   }

   /** @return array<string, mixed> */
   private function defaultSettingsForBlock(string $blockType): array {
      return match ($blockType) {
         'navbar' => [
            'brand_label' => $this->form->siteName ?: 'Mi Empresa',
            'logo_url' => '',
            'navbar_bg_color' => '#ffffff',
            'navbar_text_color' => '#0f172a',
            'navbar_link_color' => '#2563eb',
            'layout_style' => 'normal',
         ],
         'hero' => ['headline' => 'Tu propuesta principal', 'subheadline' => 'Explica rápidamente el valor de tu solución.', 'cta_text' => 'Comenzar', 'cta_url' => '/register'],
         'services' => ['title' => 'Servicios', 'items' => [['title' => 'Servicio 1', 'description' => 'Descripción breve.']]],
         'gallery' => ['title' => 'Galería', 'images' => [['url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80', 'alt' => 'Imagen 1']]],
         'testimonials' => ['title' => 'Testimonios', 'items' => [['quote' => 'Excelente servicio y resultados.', 'author' => 'Cliente', 'role' => 'CEO']]],
         'pricing' => ['title' => 'Planes', 'currency' => '$', 'plans' => [['name' => 'Starter', 'price' => '29', 'period' => 'mes', 'cta' => 'Elegir plan']]],
         'faq' => ['title' => 'Preguntas frecuentes', 'items' => [['question' => '¿Cómo funciona?', 'answer' => 'Puedes empezar en minutos.']]],
         'contact' => ['title' => 'Contacto', 'email' => '', 'phone' => '', 'address' => ''],
         'about' => ['title' => 'Sobre nosotros', 'body' => 'Cuenta tu historia en este bloque.', 'image_url' => ''],
         'story' => ['title' => 'Historia', 'milestones' => [['year' => '2026', 'event' => 'Inicio del proyecto']]],
         'achievements' => ['title' => 'Logros', 'items' => [['title' => 'Clientes', 'value' => '+100']]],
         'catalog' => ['title' => 'Catálogo', 'items' => [['name' => 'Producto base', 'price' => '$99', 'description' => 'Descripción breve.']]],
         'trust' => ['title' => 'Confían en nosotros', 'items' => [['title' => 'Empresa ejemplo']]],
         'cta' => ['title' => '¿Listo para empezar?', 'subtitle' => 'Comienza hoy con tu workspace.', 'button_text' => 'Crear cuenta', 'button_url' => '/register'],
         default => [],
      };
   }

   /** @return array<string, mixed> */
   private function normalizedSettings(LandingBlock $block): array {
      $settings = is_array($block->settings) ? $block->settings : [];

      return match ($block->block_type) {
         'navbar' => [
            'brand_label' => (string) ($settings['brand_label'] ?? ($this->form->siteName ?: 'Mi Empresa')),
            'logo_url' => (string) ($settings['logo_url'] ?? ''),
            'navbar_bg_color' => (string) ($settings['navbar_bg_color'] ?? '#ffffff'),
            'navbar_text_color' => (string) ($settings['navbar_text_color'] ?? '#0f172a'),
            'navbar_link_color' => (string) ($settings['navbar_link_color'] ?? '#2563eb'),
            'layout_style' => (string) ($settings['layout_style'] ?? 'normal'),
         ],
         'hero' => [
            'headline' => (string) ($settings['headline'] ?? ''),
            'subheadline' => (string) ($settings['subheadline'] ?? ''),
            'cta_text' => (string) ($settings['cta_text'] ?? ''),
            'cta_url' => (string) ($settings['cta_url'] ?? '#'),
         ],
         'services' => [
            'title' => (string) ($settings['title'] ?? ''),
            'items' => is_array($settings['items'] ?? null) ? $settings['items'] : [],
         ],
         'testimonials' => [
            'title' => (string) ($settings['title'] ?? ''),
            'items' => is_array($settings['items'] ?? null) ? $settings['items'] : [],
         ],
         'gallery' => [
            'title' => (string) ($settings['title'] ?? ''),
            'images' => is_array($settings['images'] ?? null) ? $settings['images'] : [],
         ],
         'pricing' => [
            'title' => (string) ($settings['title'] ?? ''),
            'currency' => (string) ($settings['currency'] ?? '$'),
            'plans' => is_array($settings['plans'] ?? null) ? $settings['plans'] : [],
         ],
         'faq' => [
            'title' => (string) ($settings['title'] ?? ''),
            'items' => is_array($settings['items'] ?? null) ? $settings['items'] : [],
         ],
         'contact' => [
            'title' => (string) ($settings['title'] ?? ''),
            'email' => (string) ($settings['email'] ?? ''),
            'phone' => (string) ($settings['phone'] ?? ''),
            'address' => (string) ($settings['address'] ?? ''),
         ],
         'about' => [
            'title' => (string) ($settings['title'] ?? ''),
            'body' => (string) ($settings['body'] ?? ''),
            'image_url' => (string) ($settings['image_url'] ?? ''),
         ],
         'story' => [
            'title' => (string) ($settings['title'] ?? ''),
            'milestones' => is_array($settings['milestones'] ?? null) ? $settings['milestones'] : [],
         ],
         'achievements' => [
            'title' => (string) ($settings['title'] ?? ''),
            'items' => is_array($settings['items'] ?? null) ? $settings['items'] : [],
         ],
         'catalog' => [
            'title' => (string) ($settings['title'] ?? ''),
            'items' => is_array($settings['items'] ?? null) ? $settings['items'] : [],
         ],
         'trust' => [
            'title' => (string) ($settings['title'] ?? ''),
            'items' => is_array($settings['items'] ?? null) ? $settings['items'] : [],
         ],
         'cta' => [
            'title' => (string) ($settings['title'] ?? ''),
            'subtitle' => (string) ($settings['subtitle'] ?? ''),
            'button_text' => (string) ($settings['button_text'] ?? ''),
            'button_url' => (string) ($settings['button_url'] ?? '/register'),
         ],
         default => $settings,
      };
   }

   /** @return array<string, string|array<int, string>> */
   private function rulesForBlock(string $blockType): array {
      return match ($blockType) {
         'navbar' => [
            'settings.brand_label' => ['nullable', 'string', 'max:120'],
            'settings.logo_url' => ['nullable', 'url', 'max:500'],
            'settings.navbar_bg_color' => ['nullable', 'string', 'max:7'],
            'settings.navbar_text_color' => ['nullable', 'string', 'max:7'],
            'settings.navbar_link_color' => ['nullable', 'string', 'max:7'],
            'settings.layout_style' => ['nullable', 'in:compact,normal,wide'],
         ],
         'hero' => [
            'settings.headline' => ['required', 'string', 'max:255'],
            'settings.subheadline' => ['required', 'string', 'max:1000'],
            'settings.cta_text' => ['required', 'string', 'max:60'],
            'settings.cta_url' => ['required', 'string', 'max:500'],
         ],
         'services' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.items' => ['required', 'array', 'min:1'],
            'settings.items.*.title' => ['required', 'string', 'max:120'],
            'settings.items.*.description' => ['required', 'string', 'max:300'],
         ],
         'testimonials' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.items' => ['required', 'array', 'min:1'],
            'settings.items.*.quote' => ['required', 'string', 'max:300'],
            'settings.items.*.author' => ['required', 'string', 'max:120'],
            'settings.items.*.role' => ['nullable', 'string', 'max:120'],
         ],
         'gallery' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.images' => ['required', 'array', 'min:1'],
            'settings.images.*.url' => ['required', 'string', 'max:500'],
            'settings.images.*.alt' => ['nullable', 'string', 'max:140'],
         ],
         'pricing' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.currency' => ['required', 'string', 'max:8'],
            'settings.plans' => ['required', 'array', 'min:1'],
            'settings.plans.*.name' => ['required', 'string', 'max:120'],
            'settings.plans.*.price' => ['required', 'string', 'max:40'],
            'settings.plans.*.period' => ['nullable', 'string', 'max:40'],
            'settings.plans.*.cta' => ['nullable', 'string', 'max:80'],
         ],
         'faq' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.items' => ['required', 'array', 'min:1'],
            'settings.items.*.question' => ['required', 'string', 'max:220'],
            'settings.items.*.answer' => ['required', 'string', 'max:600'],
         ],
         'contact' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.email' => ['nullable', 'email', 'max:180'],
            'settings.phone' => ['nullable', 'string', 'max:60'],
            'settings.address' => ['nullable', 'string', 'max:220'],
         ],
         'about' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.body' => ['required', 'string', 'max:2000'],
            'settings.image_url' => ['nullable', 'string', 'max:500'],
         ],
         'story' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.milestones' => ['required', 'array', 'min:1'],
            'settings.milestones.*.year' => ['required', 'string', 'max:40'],
            'settings.milestones.*.event' => ['required', 'string', 'max:260'],
         ],
         'achievements' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.items' => ['required', 'array', 'min:1'],
            'settings.items.*.title' => ['required', 'string', 'max:120'],
            'settings.items.*.value' => ['required', 'string', 'max:80'],
         ],
         'catalog' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.items' => ['required', 'array', 'min:1'],
            'settings.items.*.name' => ['required', 'string', 'max:120'],
            'settings.items.*.price' => ['required', 'string', 'max:80'],
            'settings.items.*.description' => ['nullable', 'string', 'max:260'],
         ],
         'trust' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.items' => ['required', 'array', 'min:1'],
            'settings.items.*.title' => ['required', 'string', 'max:120'],
         ],
         'cta' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.subtitle' => ['required', 'string', 'max:300'],
            'settings.button_text' => ['required', 'string', 'max:60'],
            'settings.button_url' => ['required', 'string', 'max:500'],
         ],
         default => ['settings' => ['array']],
      };
   }

   private function selectedBlockType(): string {
      $selectedBlock = collect($this->blocks)->firstWhere('id', $this->selectedBlockId);

      return (string) ($selectedBlock['block_type'] ?? '');
   }
}
