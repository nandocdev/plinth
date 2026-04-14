<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Livewire;

use App\Central\AffiliateModule\Actions\ListReferralConversionsAction;
use App\Central\AffiliateModule\Models\ReferralConversion;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Historial de Conversiones')]
final class ReferralConversions extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public string $search = '';

   public function mount(): void {
      $this->authorize('viewAny', ReferralConversion::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
   }

   public function render(ListReferralConversionsAction $listConversions): View {
      return view('affiliate::livewire.referral-conversions', [
         'conversions' => $listConversions->execute($this->search, 15),
      ]);
   }
}
