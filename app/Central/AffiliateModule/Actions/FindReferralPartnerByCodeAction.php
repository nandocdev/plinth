<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Actions;

use App\Central\AffiliateModule\Models\ReferralPartner;

final class FindReferralPartnerByCodeAction {
   public function execute(string $code): ?ReferralPartner {
      return ReferralPartner::query()
         ->whereRaw('UPPER(code) = ?', [strtoupper($code)])
         ->where('is_active', true)
         ->first();
   }
}
