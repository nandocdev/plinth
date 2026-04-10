<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\FileUploadModule\DTOs;

use Illuminate\Http\UploadedFile;

final readonly class StoreTenantUploadedFileData {
   public function __construct(
      public UploadedFile $file,
      public ?string $folder,
      public ?int $uploadedByUserId,
   ) {
   }
}
