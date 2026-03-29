<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Listeners;

use App\Central\AffiliateModule\Actions\RegisterReferralConversionAction;
use App\Central\AffiliateModule\DTOs\RegisterReferralConversionData;
use App\Central\TenantProvisioningModule\Events\TenantCreatedFromCentral;

final class RegisterReferralConversionOnTenantCreated {
   public function __construct(
      private readonly RegisterReferralConversionAction $registerConversion,
   ) {
   }

   public function handle(TenantCreatedFromCentral $event): void {
      $metadata = $event->tenant->metadata();
      $referralCode = $metadata['referral_code'] ?? null;

      if (! is_string($referralCode) || trim($referralCode) === '') {
         return;
      }

      $this->registerConversion->execute(new RegisterReferralConversionData(
         partnerCode: $referralCode,
         tenantId: (string) $event->tenant->id,
         referredEmail: is_string($metadata['owner_email'] ?? null) ? (string) $metadata['owner_email'] : null,
         metadata: [
            'source' => 'tenant_onboarding',
            'domain' => $event->tenant->domains()->value('domain'),
         ],
      ));
   }
}
