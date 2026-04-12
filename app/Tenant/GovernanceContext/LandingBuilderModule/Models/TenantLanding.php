<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TenantLanding extends Model {
   use HasFactory;

   protected $table = 'tenant_landings';

   protected $fillable = [
      'template_key',
      'status',
      'font_family',
      'primary_color',
      'global_settings',
   ];

   protected function casts(): array {
      return [
         'global_settings' => 'array',
      ];
   }

   public function blocks(): HasMany {
      return $this->hasMany(LandingBlock::class, 'tenant_landing_id');
   }

   /** @return array<int, array<string, mixed>> */
   public static function availableTemplates(): array {
      return collect(config('landing_templates', []))
         ->filter(fn(mixed $template): bool => is_array($template) && isset($template['blocks']))
         ->map(fn(array $template, string $key) => [
            'key' => $key,
            'name' => $template['name'] ?? ucfirst($key),
            'vibe' => $template['vibe'] ?? '',
            'primary_color' => $template['primary_color'] ?? '#2563eb',
            'font_family' => $template['font_family'] ?? 'instrument',
         ])
         ->values()
         ->all();
   }

   public function applyTemplate(string $templateKey): void {
      /** @var array<string, mixed>|null $template */
      $template = config('landing_templates.' . $templateKey);

      if (! is_array($template)) {
         return;
      }

      $globalSettings = is_array($this->global_settings) ? $this->global_settings : [];
      $templateSettings = is_array($template['global_settings'] ?? null) ? $template['global_settings'] : [];

      $this->update([
         'template_key' => $templateKey,
         'font_family' => (string) ($template['font_family'] ?? 'instrument'),
         'primary_color' => (string) ($template['primary_color'] ?? '#2563eb'),
         'global_settings' => [
            ...$globalSettings,
            ...$templateSettings,
            'site_name' => $globalSettings['site_name'] ?? 'Mi Empresa',
            'default_cta' => $globalSettings['default_cta'] ?? 'Comenzar',
         ],
      ]);

      $this->blocks()->delete();

      $this->blocks()->create([
         'block_type' => 'navbar',
         'order' => 1,
         'is_active' => true,
         'settings' => [
            'brand_label' => (string) ($globalSettings['site_name'] ?? 'Mi Empresa'),
            'logo_url' => '',
            'navbar_bg_color' => '#ffffff',
            'navbar_text_color' => '#0f172a',
            'navbar_link_color' => (string) ($this->primary_color ?? '#2563eb'),
            'layout_style' => 'normal',
         ],
      ]);

      foreach (($template['blocks'] ?? []) as $index => $block) {
         if (($block['type'] ?? null) === 'navbar') {
            continue;
         }

         $this->blocks()->create([
            'block_type' => $block['type'],
            'order' => $index + 2,
            'is_active' => true,
            'settings' => is_array($block['defaults'] ?? null) ? $block['defaults'] : [],
         ]);
      }
   }

   public function ensureNavbarBlockExists(): void {
      $existing = $this->blocks()->where('block_type', 'navbar')->first();

      if ($existing instanceof LandingBlock) {
         if ((int) $existing->order !== 1) {
            $existing->update(['order' => 1, 'is_active' => true]);
         }

         return;
      }

      $this->blocks()->create([
         'block_type' => 'navbar',
         'order' => 1,
         'is_active' => true,
         'settings' => [
            'brand_label' => (string) (($this->global_settings['site_name'] ?? null) ?: 'Mi Empresa'),
            'logo_url' => '',
            'navbar_bg_color' => '#ffffff',
            'navbar_text_color' => '#0f172a',
            'navbar_link_color' => (string) ($this->primary_color ?? '#2563eb'),
            'layout_style' => 'normal',
         ],
      ]);

      $blocks = $this->blocks()
         ->where('block_type', '!=', 'navbar')
         ->orderBy('order')
         ->orderBy('id')
         ->get();

      foreach ($blocks as $index => $block) {
         $block->update(['order' => $index + 2]);
      }
   }
}
