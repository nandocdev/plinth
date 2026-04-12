<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Services;

use App\Tenant\GovernanceContext\LandingBuilderModule\Models\LandingBlock;
use App\Tenant\GovernanceContext\LandingBuilderModule\Models\TenantLanding;
use Illuminate\Support\Collection;

final class LandingRendererService {
   /**
    * @return array<string, mixed>
    */
   public function compile(TenantLanding $landing): array {
      /** @var Collection<int, LandingBlock> $activeBlocks */
      $activeBlocks = $landing->blocks()
         ->where('is_active', true)
         ->orderBy('order')
         ->get();

      $hero = $activeBlocks->firstWhere('block_type', 'hero');
      $heroSettings = is_array($hero?->settings) ? $hero->settings : [];

      $globalSettings = is_array($landing->global_settings) ? $landing->global_settings : [];
      $bgMode = (string) ($globalSettings['bg_mode'] ?? 'light');
      $fontFamily = (string) ($landing->font_family ?? 'instrument');

      return [
         'siteName' => (string) ($globalSettings['site_name'] ?? 'Mi Empresa'),
         'meta' => [
            'title' => (string) ($globalSettings['site_name'] ?? 'Mi Empresa'),
            'description' => (string) ($heroSettings['subheadline'] ?? ''),
         ],
         'theme' => [
            'primary' => (string) ($landing->primary_color ?? '#2563eb'),
            'neutral' => (string) ($globalSettings['color_neutral'] ?? '#e2e8f0'),
            'accent' => (string) ($globalSettings['color_accent'] ?? '#0f172a'),
            'bg_mode' => $bgMode,
            'bg_page' => $this->bgPage($bgMode),
            'bg_section' => $this->bgSection($bgMode),
            'bg_card' => $this->bgCard($bgMode),
            'text_primary' => $this->textPrimary($bgMode),
            'text_secondary' => $this->textSecondary($bgMode),
            'font_family' => $fontFamily,
            'font_stack' => $this->fontStack($fontFamily),
         ],
         'content' => [
            'headline' => (string) ($heroSettings['headline'] ?? 'Bienvenido'),
            'description' => (string) ($heroSettings['subheadline'] ?? 'Presenta tu propuesta de valor con claridad.'),
            'cta' => (string) ($heroSettings['cta_text'] ?? 'Comenzar'),
         ],
         'blocks' => $activeBlocks,
         'isPublished' => $landing->status === 'published',
      ];
   }

   private function bgPage(string $bgMode): string {
      return match ($bgMode) {
         'dark' => '#09090b',
         'soft' => '#fafaf9',
         default => '#ffffff',
      };
   }

   private function bgSection(string $bgMode): string {
      return match ($bgMode) {
         'dark' => '#18181b',
         'soft' => '#f5f5f4',
         default => '#f8fafc',
      };
   }

   private function bgCard(string $bgMode): string {
      return match ($bgMode) {
         'dark' => '#27272a',
         'soft' => '#ffffff',
         default => '#ffffff',
      };
   }

   private function textPrimary(string $bgMode): string {
      return match ($bgMode) {
         'dark' => '#fafafa',
         default => '#0f172a',
      };
   }

   private function textSecondary(string $bgMode): string {
      return match ($bgMode) {
         'dark' => '#d4d4d8',
         default => '#475569',
      };
   }

   private function fontStack(string $fontFamily): string {
      return match ($fontFamily) {
         'slab' => 'Rockwell, Georgia, Cambria, serif',
         'mono' => '"IBM Plex Mono", "SFMono-Regular", Consolas, monospace',
         'sans' => 'Instrument Sans, Inter, system-ui, sans-serif',
         default => 'Instrument Sans, system-ui, sans-serif',
      };
   }
}
