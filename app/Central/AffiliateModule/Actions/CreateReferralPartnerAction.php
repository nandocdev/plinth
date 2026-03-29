<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Actions;

use App\Central\AffiliateModule\DTOs\CreateReferralPartnerData;
use App\Central\AffiliateModule\Models\ReferralPartner;
use Illuminate\Support\Facades\DB;

final class CreateReferralPartnerAction {
   public function execute(CreateReferralPartnerData $data): ReferralPartner {
      /** @var ReferralPartner $partner */
      $partner = DB::connection('central')->transaction(function () use ($data): ReferralPartner {
         /** @var ReferralPartner $created */
         $created = ReferralPartner::query()->create([
            'code' => strtoupper($data->code),
            'name' => $data->name,
            'email' => strtolower($data->email),
            'payout_type' => $data->payoutType,
            'payout_value' => $data->payoutValue,
            'is_active' => $data->isActive,
            'notes' => $data->notes,
         ]);

         return $created;
      });

      return $partner;
   }
}
