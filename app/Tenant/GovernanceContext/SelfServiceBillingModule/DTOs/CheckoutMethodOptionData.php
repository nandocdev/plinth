<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\SelfServiceBillingModule\DTOs;

final readonly class CheckoutMethodOptionData {
   public function __construct(
      public string $methodType,
      public string $provider,
      public string $label,
      public string $description,
      public bool $manualConfirmationRequired,
      public string $statusMessage,
   ) {
   }

   /**
    * @return array{method_type: string, provider: string, label: string, description: string, manual_confirmation_required: bool, status_message: string}
    */
   public function toArray(): array {
      return [
         'method_type' => $this->methodType,
         'provider' => $this->provider,
         'label' => $this->label,
         'description' => $this->description,
         'manual_confirmation_required' => $this->manualConfirmationRequired,
         'status_message' => $this->statusMessage,
      ];
   }
}
