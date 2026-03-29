<?php

declare(strict_types=1);

namespace Database\Factories\Central\AffiliateModule;

use App\Central\AffiliateModule\Models\ReferralConversion;
use App\Central\AffiliateModule\Models\ReferralPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferralConversion>
 */
final class ReferralConversionFactory extends Factory {
   protected $model = ReferralConversion::class;

   public function definition(): array {
      return [
         'referral_partner_id' => ReferralPartner::factory(),
         'tenant_id' => 'tenant-' . fake()->uuid(),
         'referred_email' => fake()->safeEmail(),
         'status' => ReferralConversion::STATUS_QUALIFIED,
         'commission_cents' => 0,
         'currency' => 'USD',
         'converted_at' => now(),
         'metadata' => [
            'source' => 'factory',
         ],
      ];
   }
}
