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

      foreach (($template['blocks'] ?? []) as $index => $block) {
         $this->blocks()->create([
            'block_type' => $block['type'],
            'order' => $index + 1,
            'is_active' => true,
            'settings' => is_array($block['defaults'] ?? null) ? $block['defaults'] : [],
         ]);
      }
   }
}
