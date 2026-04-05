<?php

declare(strict_types=1);

namespace App\Tenant\AddonsModule\Livewire;

use App\Tenant\AddonsModule\Actions\ListAvailableAddonsAction;
use App\Tenant\AddonsModule\Actions\ToggleAddonAction;
use App\Tenant\AddonsModule\DTOs\AddonStateData;
use App\Tenant\AddonsModule\Enums\AvailableAddon;
use App\Tenant\AddonsModule\Models\TenantAddon;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Addons')]
final class AddonsManager extends Component {
   use AuthorizesRequests;

   public ?string $successMessage = null;
   public ?string $errorMessage   = null;

   public function mount(): void {
      $this->authorize('viewAny', TenantAddon::class);
   }

   public function toggle(string $slug, ToggleAddonAction $action): void {
      $this->authorize('manage', TenantAddon::class);

      $addon = AvailableAddon::tryFrom($slug);

      if ($addon === null) {
         $this->errorMessage   = "Addon '{$slug}' no reconocido.";
         $this->successMessage = null;

         return;
      }

      $record = $action->execute($addon);

      $this->successMessage = $record->is_active
         ? "Addon «{$addon->label()}» activado correctamente."
         : "Addon «{$addon->label()}» desactivado correctamente.";

      $this->errorMessage = null;
   }

   public function render(ListAvailableAddonsAction $listAddons): View {
      /** @var list<AddonStateData> $addons */
      $addons = $listAddons->execute();

      // Agrupa por categoría para la vista
      $grouped = [];
      foreach ($addons as $addon) {
         $grouped[$addon->category->label()][] = $addon;
      }

      return view('addons::livewire.addons-manager', [
         'grouped' => $grouped,
      ]);
   }
}
