<?php

declare(strict_types=1);

namespace App\Tenant\PlatformContext\WorkspaceModule\Livewire;

use App\Tenant\IdentityContext\AuthenticationModule\Models\User;
use App\Tenant\PlatformContext\WorkspaceModule\Actions\GetTenantDashboardDataAction;
use App\Tenant\PlatformContext\WorkspaceModule\DTOs\TenantDashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Dashboard Tenant')]
final class TenantDashboard extends Component {
   public function mount(): void {
      if (! Auth::guard('tenant')->check()) {
         $this->redirect('/login', navigate: true);
      }
   }

   public function logout(): void {
      Auth::guard('tenant')->logout();
      session()->invalidate();
      session()->regenerateToken();

      $this->redirect('/login', navigate: true);
   }

   public function render(GetTenantDashboardDataAction $action): View {
      /** @var User|null $user */
      $user = Auth::guard('tenant')->user();

      if (! $user instanceof User) {
         abort(403);
      }

      return view('workspace::livewire.tenant-dashboard', [
         'dashboard' => $action->execute($user),
      ]);
   }
}
