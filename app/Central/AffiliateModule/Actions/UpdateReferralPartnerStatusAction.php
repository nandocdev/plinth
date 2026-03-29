<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Actions;

use App\Central\AffiliateModule\Models\ReferralPartner;
use Illuminate\Support\Facades\DB;

final class UpdateReferralPartnerStatusAction {
   public function execute(int $partnerId, bool $isActive): ReferralPartner {
      /** @var ReferralPartner $partner */
      $partner = DB::connection('central')->transaction(function () use ($partnerId, $isActive): ReferralPartner {
         /** @var ReferralPartner $partner */
         $partner = ReferralPartner::query()->findOrFail($partnerId);

         $partner->update([
            'is_active' => $isActive,
         ]);

         return $partner;
      });

      return $partner;
   }
}
