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
   }

   public function selectBlock(int $blockId): void {
      $block = LandingBlock::query()->where('tenant_landing_id', $this->landingId)->findOrFail($blockId);

      $this->selectedBlockId = $block->id;
      $this->editingBlockActive = (bool) $block->is_active;
      $this->editingSettings = $this->normalizedSettings($block);
   }

   public function toggleBlock(): void {
      $this->editingBlockActive = ! $this->editingBlockActive;
   }

   public function saveBlock(UpdateLandingBlockSettingsAction $action): void {
      $block = LandingBlock::query()->where('tenant_landing_id', $this->landingId)->findOrFail($this->selectedBlockId);

      $validated = Validator::make(
         ['settings' => $this->editingSettings],
         $this->rulesForBlock($block->block_type),
      )->validate();

      $action->execute($block, $validated['settings'], $this->editingBlockActive);

      $this->message = 'Bloque actualizado correctamente.';
      $this->refreshBlocks();
      $this->selectBlock($block->id);
   }

   public function render(): View {
      $selectedBlock = collect($this->blocks)->firstWhere('id', $this->selectedBlockId);

      return view('landing-builder::livewire.landing-builder', [
         'publicUrl' => route('tenant.landing.public', ['tenantDomain' => request()->route('tenantDomain')]),
         'previewUrl' => route('tenant.landing.preview', ['tenantDomain' => request()->route('tenantDomain')]),
         'selectedBlockType' => (string) ($selectedBlock['block_type'] ?? ''),
         'availableTemplates' => TenantLanding::availableTemplates(),
      ]);
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

   /** @return array<string, mixed> */
   private function normalizedSettings(LandingBlock $block): array {
      $settings = is_array($block->settings) ? $block->settings : [];

      return match ($block->block_type) {
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
         'cta' => [
            'settings.title' => ['required', 'string', 'max:180'],
            'settings.subtitle' => ['required', 'string', 'max:300'],
            'settings.button_text' => ['required', 'string', 'max:60'],
            'settings.button_url' => ['required', 'string', 'max:500'],
         ],
         default => ['settings' => ['array']],
      };
   }
}
