<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Livewire\Forms;

use App\Central\BillingModule\Models\Plan;
use App\Central\TenantProvisioningModule\Models\Domain;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class PublicTenantSignupForm extends Form {
   #[Validate('required|string|max:100')]
   public string $companyName = '';

   #[Validate([
      'required',
      'string',
      'max:40',
      'regex:/^[a-z0-9\-]+$/',
   ])]
   public string $subdomain = '';

   #[Validate('required|string|max:100')]
   public string $adminName = '';

   #[Validate('required|email|max:255')]
   public string $adminEmail = '';

   #[Validate('required|string|min:8|max:255')]
   public string $adminPassword = '';

   #[Validate('required|string|same:adminPassword')]
   public string $adminPasswordConfirmation = '';

   #[Validate('required|integer|exists:plans,id')]
   public int $planId = 0;

   #[Validate('required|accepted')]
   public bool $terms = false;

   /**
    * @return array<string, list<string>|string>
    */
   public function rulesForStep(int $step): array {
      return match ($step) {
         1 => [
            'form.companyName' => ['required', 'string', 'max:100'],
            'form.subdomain' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9\-]+$/'],
         ],
         2 => [
            'form.planId' => ['required', 'integer', 'exists:plans,id'],
         ],
         3 => [
            'form.adminName' => ['required', 'string', 'max:100'],
            'form.adminEmail' => ['required', 'email', 'max:255'],
            'form.adminPassword' => ['required', 'string', 'min:8', 'max:255'],
            'form.adminPasswordConfirmation' => ['required', 'string', 'same:form.adminPassword'],
         ],
         4 => [
            'form.terms' => ['required', 'accepted'],
         ],
         default => $this->rulesForAllSteps(),
      };
   }

   /**
    * @return array<string, list<string>|string>
    */
   public function rulesForAllSteps(): array {
      return array_merge(
         $this->rulesForStep(1),
         $this->rulesForStep(2),
         $this->rulesForStep(3),
         $this->rulesForStep(4),
      );
   }

   public function validateSubdomainAvailable(): bool {
      $baseHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
      $fullDomain = "{$this->subdomain}.{$baseHost}";

      return ! Domain::query()->where('domain', $fullDomain)->exists();
   }

   public function availablePlans(): \Illuminate\Database\Eloquent\Collection {
      return Plan::query()->where('is_active', true)->orderBy('sort_order')->get();
   }
}
