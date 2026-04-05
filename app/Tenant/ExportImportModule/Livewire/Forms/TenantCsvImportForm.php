<?php

declare(strict_types=1);

namespace App\Tenant\ExportImportModule\Livewire\Forms;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

final class TenantCsvImportForm extends Form {
   public ?TemporaryUploadedFile $file = null;

   public function rules(): array {
      return [
         'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
      ];
   }

   public function resetFile(): void {
      $this->file = null;
   }
}
