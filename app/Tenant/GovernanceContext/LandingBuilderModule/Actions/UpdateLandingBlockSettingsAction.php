<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Actions;

use App\Tenant\GovernanceContext\LandingBuilderModule\Models\LandingBlock;
use Illuminate\Support\Facades\DB;

final class UpdateLandingBlockSettingsAction {
   /**
    * @param  array<string, mixed>  $settings
    */
   public function execute(LandingBlock $block, array $settings, bool $isActive): LandingBlock {
      return DB::transaction(function () use ($block, $settings, $isActive): LandingBlock {
         $block->update([
            'settings' => $settings,
            'is_active' => $isActive,
         ]);

         return $block->refresh();
      });
   }
}
