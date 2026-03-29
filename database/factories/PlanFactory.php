<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Central\BillingModule\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
final class PlanFactory extends Factory {
   protected $model = Plan::class;

   /**
    * @return array<string, mixed>
    */
   public function definition(): array {
      return [
         'name' => 'Plan ' . fake()->unique()->word(),
         'slug' => fake()->unique()->slug(2),
         'price_monthly_cents' => fake()->numberBetween(1000, 10000),
         'price_yearly_cents' => fake()->optional()->numberBetween(10000, 100000),
         'trial_days' => fake()->numberBetween(0, 30),
         'features' => ['api_access'],
         'is_active' => true,
         'sort_order' => fake()->numberBetween(1, 100),
      ];
   }
}
