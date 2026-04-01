<?php

declare(strict_types=1);

namespace App\Tenant\FeatureFlagsModule\Livewire;

use App\Tenant\FeatureFlagsModule\Actions\EvaluateTenantUsageLimitsAction;
use App\Tenant\FeatureFlagsModule\Actions\GetTenantPlanFeaturesAction;
use App\Tenant\FeatureFlagsModule\DTOs\PlanFeaturesData;
use App\Tenant\FeatureFlagsModule\DTOs\UsageLimitsEvaluationData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Características del Plan')]
final class PlanFeaturesOverview extends Component {
   public function mount(): void {
      if (! Auth::guard('tenant')->check()) {
         $this->redirect('/login', navigate: true);
      }
   }

   public function render(
      GetTenantPlanFeaturesAction $featuresAction,
      EvaluateTenantUsageLimitsAction $limitsAction,
   ): View {
      $tenantId = (string) tenant('id');

      /** @var PlanFeaturesData $features */
      $features = $featuresAction->execute($tenantId);

      /** @var UsageLimitsEvaluationData $limits */
      $limits = $limitsAction->execute($tenantId);

      return view('feature-flags::livewire.plan-features-overview', [
         'features' => $features,
         'limits' => $limits,
      ]);
   }
}
