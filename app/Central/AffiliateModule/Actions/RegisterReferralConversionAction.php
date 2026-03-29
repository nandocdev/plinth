<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Actions;

use App\Central\AffiliateModule\DTOs\RegisterReferralConversionData;
use App\Central\AffiliateModule\Models\ReferralConversion;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class RegisterReferralConversionAction {
   public function __construct(
      private readonly FindReferralPartnerByCodeAction $findPartner,
   ) {
   }

   public function execute(RegisterReferralConversionData $data): ?ReferralConversion {
      $partner = $this->findPartner->execute($data->partnerCode);

      if ($partner === null) {
         return null;
      }

      /** @var ReferralConversion $conversion */
      $conversion = DB::connection('central')->transaction(function () use ($partner, $data): ReferralConversion {
         /** @var ReferralConversion $conversion */
         $conversion = ReferralConversion::query()->firstOrNew([
            'tenant_id' => $data->tenantId,
         ]);

         $conversion->fill([
            'referral_partner_id' => $partner->id,
            'referred_email' => $data->referredEmail !== null ? strtolower($data->referredEmail) : null,
            'status' => ReferralConversion::STATUS_QUALIFIED,
            'commission_cents' => 0,
            'currency' => 'USD',
            'converted_at' => CarbonImmutable::now(),
            'metadata' => $data->metadata,
         ]);

         $conversion->save();

         return $conversion->refresh();
      });

      return $conversion;
   }
}
