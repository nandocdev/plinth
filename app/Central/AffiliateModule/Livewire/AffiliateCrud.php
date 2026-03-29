<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Livewire;

use App\Central\AffiliateModule\Actions\CreateReferralPartnerAction;
use App\Central\AffiliateModule\Actions\ListReferralConversionsAction;
use App\Central\AffiliateModule\Actions\ListReferralPartnersAction;
use App\Central\AffiliateModule\Actions\UpdateReferralPartnerStatusAction;
use App\Central\AffiliateModule\DTOs\CreateReferralPartnerData;
use App\Central\AffiliateModule\Livewire\Forms\ReferralPartnerForm;
use App\Central\AffiliateModule\Models\ReferralConversion;
use App\Central\AffiliateModule\Models\ReferralPartner;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Affiliate and Referral Management')]
final class AffiliateCrud extends Component {
   use AuthorizesRequests;
   use WithPagination;

   public ReferralPartnerForm $form;

   public string $partnersSearch = '';

   public string $conversionsSearch = '';

   public function mount(): void {
      $this->authorize('viewAny', ReferralPartner::class);
      $this->authorize('viewAny', ReferralConversion::class);
   }

   public function updatedPartnersSearch(): void {
      $this->resetPage('partnersPage');
   }

   public function updatedConversionsSearch(): void {
      $this->resetPage('conversionsPage');
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
      $this->resetPage('partnersPage');
      session()->flash('status', 'Afiliado creado correctamente.');
   }

   public function togglePartnerStatus(int $partnerId, UpdateReferralPartnerStatusAction $action): void {
      /** @var ReferralPartner $partner */
      $partner = ReferralPartner::query()->findOrFail($partnerId);
      $this->authorize('update', $partner);

      $action->execute($partner->id, ! $partner->is_active);

      session()->flash('status', 'Estado de afiliado actualizado.');
   }

   public function render(
      ListReferralPartnersAction $listPartners,
      ListReferralConversionsAction $listConversions,
   ): View {
      return view('affiliate::livewire.affiliate-crud', [
         'partners' => $listPartners->execute($this->partnersSearch, 10, 'partnersPage'),
         'conversions' => $listConversions->execute($this->conversionsSearch, 10, 'conversionsPage'),
      ]);
   }
}
