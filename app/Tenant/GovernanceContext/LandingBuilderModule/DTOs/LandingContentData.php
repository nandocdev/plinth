<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\DTOs;

final readonly class LandingContentData {
   public function __construct(
      public string $siteName,
      public string $defaultCta,
      public string $primaryColor,
      public string $status,
   ) {
   }

   public static function fromArray(array $data): self {
      return new self(
         siteName: (string) ($data['site_name'] ?? 'Mi Empresa'),
         defaultCta: (string) ($data['default_cta'] ?? 'Comenzar'),
         primaryColor: (string) ($data['primary_color'] ?? '#2563eb'),
         status: (string) ($data['status'] ?? 'draft'),
      );
   }
}
