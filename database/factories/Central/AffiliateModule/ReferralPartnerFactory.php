<?php

declare(strict_types=1);

namespace Database\Factories\Central\AffiliateModule;

use App\Central\AffiliateModule\Models\ReferralPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferralPartner>
 */
final class ReferralPartnerFactory extends Factory {
   protected $model = ReferralPartner::class;

   public function definition(): array {
      return [
         'code' => strtoupper(fake()->bothify('PARTNER-##??')),
         'name' => fake()->company(),
         'email' => fake()->unique()->safeEmail(),
         'payout_type' => fake()->randomElement(['fixed', 'percentage']),
         'payout_value' => fake()->randomFloat(2, 5, 25),
         'is_active' => true,
         'notes' => fake()->optional()->sentence(),
      ];
   }
}
