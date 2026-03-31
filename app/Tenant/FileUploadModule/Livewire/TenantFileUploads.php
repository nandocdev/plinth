<?php

declare(strict_types=1);

namespace App\Tenant\FileUploadModule\Livewire;

use App\Tenant\AuthenticationModule\Models\User;
use App\Tenant\FileUploadModule\Actions\DeleteTenantUploadedFileAction;
use App\Tenant\FileUploadModule\Actions\ListTenantUploadedFilesAction;
use App\Tenant\FileUploadModule\Actions\StoreTenantUploadedFileAction;
use App\Tenant\FileUploadModule\DTOs\StoreTenantUploadedFileData;
use App\Tenant\FileUploadModule\Livewire\Forms\TenantFileUploadForm;
use App\Tenant\FileUploadModule\Models\TenantUploadedFile;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Archivos del tenant')]
final class TenantFileUploads extends Component {
   use WithPagination;
   use WithFileUploads;

   public TenantFileUploadForm $form;

   public string $search = '';
   public int $perPage = 10;

   public ?int $fileIdToDelete = null;
   public ?string $successMessage = null;

   public function mount(): void {
      $this->authorize('viewAny', TenantUploadedFile::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
   }

   public function upload(StoreTenantUploadedFileAction $action): void {
      $this->successMessage = null;
      $this->authorize('create', TenantUploadedFile::class);
      $this->form->validate();

      /** @var User|null $user */
      $user = Auth::guard('tenant')->user();

      if (! $user instanceof User) {
         $this->redirect('/login', navigate: true);

         return;
      }

      if ($this->form->file === null) {
         throw new \RuntimeException('Archivo no disponible para carga.');
      }

      $action->execute(new StoreTenantUploadedFileData(
         file: $this->form->file,
         folder: $this->form->folder,
         uploadedByUserId: $user->id,
      ));

      $this->form->resetFileInput();
      $this->successMessage = 'Archivo subido correctamente.';
      $this->resetPage();
   }

   public function confirmDelete(int $fileId): void {
      $this->authorize('delete', TenantUploadedFile::class);
      $this->fileIdToDelete = $fileId;
   }

   public function deleteConfirmed(DeleteTenantUploadedFileAction $action): void {
      $this->authorize('delete', TenantUploadedFile::class);

      if ($this->fileIdToDelete === null) {
         return;
      }

      $action->execute($this->fileIdToDelete);
      $this->fileIdToDelete = null;
      $this->successMessage = 'Archivo eliminado correctamente.';
   }

   public function cancelDelete(): void {
      $this->fileIdToDelete = null;
   }

   public function render(ListTenantUploadedFilesAction $action): View {
      return view('file-upload::livewire.tenant-file-uploads', [
         'files' => $action->execute($this->search, $this->perPage, $this->getPage()),
      ]);
   }
}
