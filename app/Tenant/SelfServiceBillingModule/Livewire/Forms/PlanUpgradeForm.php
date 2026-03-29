<?php

declare(strict_types=1);

namespace App\Tenant\SelfServiceBillingModule\Livewire\Forms;

use App\Central\BillingModule\Models\Plan;
use Livewire\Form;

final class PlanUpgradeForm extends Form {
   public ?int $planId = null;

   public string $billingPeriod = 'monthly';

   /**
    * @return array<string, list<string|object>>
    */
   public function rules(): array {
      $validPlanIds = Plan::on('central')
         ->where('is_active', true)
         ->pluck('id')
         ->all();

      return [
         'planId' => ['required', 'integer', 'in:' . implode(',', $validPlanIds)],
         'billingPeriod' => ['required', 'string', 'in:monthly,yearly'],
      ];
   }

   /**
    * @return array<string, string>
    */
   public function messages(): array {
      return [
         'planId.required' => 'Selecciona un plan.',
         'planId.in' => 'El plan seleccionado no es válido.',
         'billingPeriod.in' => 'El período de facturación debe ser mensual o anual.',
      ];
   }
}
