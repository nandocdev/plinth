<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

final class PlanForm extends Form {
   public string $name = '';

   public string $slug = '';

   public int $priceMonthlyCents = 0;

   public ?int $priceYearlyCents = null;

   public int $trialDays = 0;

   public string $features = '';

   public bool $isActive = true;

   public int $sortOrder = 0;

   /**
    * @return array{name: string, slug: string, priceMonthlyCents: int, priceYearlyCents: ?int, trialDays: int, features: array<int, string>, isActive: bool, sortOrder: int}
    */
   public function payload(?int $planId = null): array {
      $this->validate([
         'name' => ['required', 'string', 'min:3', 'max:120', Rule::unique('plans', 'name')->ignore($planId)],
         'slug' => ['required', 'string', 'min:3', 'max:120', 'regex:/^[a-z0-9\-]+$/', Rule::unique('plans', 'slug')->ignore($planId)],
         'priceMonthlyCents' => ['required', 'integer', 'min:1'],
         'priceYearlyCents' => ['nullable', 'integer', 'min:1'],
         'trialDays' => ['required', 'integer', 'min:0', 'max:365'],
         'features' => ['nullable', 'string', 'max:3000'],
         'isActive' => ['boolean'],
         'sortOrder' => ['required', 'integer', 'min:0', 'max:999'],
      ]);

      return [
         'name' => trim($this->name),
         'slug' => trim(strtolower($this->slug)),
         'priceMonthlyCents' => $this->priceMonthlyCents,
         'priceYearlyCents' => $this->priceYearlyCents,
         'trialDays' => $this->trialDays,
         'features' => $this->normalizeFeatures($this->features),
         'isActive' => $this->isActive,
         'sortOrder' => $this->sortOrder,
      ];
   }

   /**
    * @return array<int, string>
    */
   private function normalizeFeatures(string $raw): array {
      $items = preg_split('/[,\n]+/', $raw) ?: [];

      $normalized = [];

      foreach ($items as $item) {
         $feature = trim(strtolower($item));

         if ($feature === '') {
            continue;
         }

         $normalized[] = $feature;
      }

      return array_values(array_unique($normalized));
   }

   public function fillFromPlan(array $data): void {
      $this->name = (string) ($data['name'] ?? '');
      $this->slug = (string) ($data['slug'] ?? '');
      $this->priceMonthlyCents = (int) ($data['price_monthly_cents'] ?? 0);
      $this->priceYearlyCents = isset($data['price_yearly_cents']) ? (int) $data['price_yearly_cents'] : null;
      $this->trialDays = (int) ($data['trial_days'] ?? 0);
      $this->features = implode(', ', is_array($data['features'] ?? null) ? $data['features'] : []);
      $this->isActive = (bool) ($data['is_active'] ?? true);
      $this->sortOrder = (int) ($data['sort_order'] ?? 0);
   }

   public function clear(): void {
      $this->reset();
      $this->isActive = true;
      $this->priceMonthlyCents = 0;
      $this->trialDays = 0;
      $this->sortOrder = 0;
   }
}
