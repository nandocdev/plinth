<?php

declare(strict_types=1);

namespace App\Tenant\FileUploadModule\Livewire\Forms;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

final class TenantFileUploadForm extends Form {
   public ?TemporaryUploadedFile $file = null;
   public string $folder = '';

   /**
    * @return array<string, list<string>>
    */
   public function rules(): array {
      return [
         'file' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,csv,txt'],
         'folder' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\/-]*$/'],
      ];
   }

   public function resetFileInput(): void {
      $this->file = null;
      $this->folder = '';
   }
}
