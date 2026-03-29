<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class TenantProvisioningHookData {
   /**
    * @param list<string> $command
    * @param array<string, string> $environment
    */
   public function __construct(
      public string $name,
      public string $driver,
      public array $command,
      public ?string $workingDirectory,
      public int $timeout,
      public array $environment,
   ) {
   }
}
