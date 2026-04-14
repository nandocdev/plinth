<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Livewire;

use App\Central\AffiliateModule\Actions\CreateReferralPartnerAction;
use App\Central\AffiliateModule\Actions\ListReferralPartnersAction;
use App\Central\AffiliateModule\Actions\UpdateReferralPartnerStatusAction;
use App\Central\AffiliateModule\DTOs\CreateReferralPartnerData;
use App\Central\AffiliateModule\Livewire\Forms\ReferralPartnerForm;
use App\Central\AffiliateModule\Models\ReferralPartner;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Gestión de Partners Afiliados')]
final class PartnerManagement extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public ReferralPartnerForm $form;
   public string $search = '';

   public function mount(): void {
      $this->authorize('viewAny', ReferralPartner::class);
   }

   public function updatedSearch(): void {
      $this->resetPage();
   }

   public function createPartner(CreateReferralPartnerAction $action): void {
      $this->authorize('create', ReferralPartner::class);

      $payload = $this->form->payload();

      $action->execute(new CreateReferralPartnerData(
         code: $payload['code'],
         name: $payload['name'],
         email: $payload['email'],
         payoutType: $payload['payoutType'],
         payoutValue: $payload['payoutValue'],
         isActive: $payload['isActive'],
         notes: $payload['notes'],
      ));

      $this->form->clear();
      session()->flash('status', 'Afiliado creado correctamente.');
      $this->resetPage();
   }

   public function togglePartnerStatus(int $partnerId, UpdateReferralPartnerStatusAction $action): void {
      /** @var ReferralPartner $partner */
      $partner = ReferralPartner::query()->findOrFail($partnerId);
      $this->authorize('update', $partner);

      $action->execute($partner->id, ! $partner->is_active);
      session()->flash('status', 'Estado de afiliado actualizado.');
   }

   public function render(ListReferralPartnersAction $listPartners): View {
      return view('affiliate::livewire.partner-management', [
         'partners' => $listPartners->execute($this->search, 15),
      ]);
   }
}
