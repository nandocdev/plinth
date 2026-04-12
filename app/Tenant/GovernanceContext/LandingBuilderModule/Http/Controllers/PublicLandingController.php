<?php

declare(strict_types=1);

namespace App\Tenant\GovernanceContext\LandingBuilderModule\Http\Controllers;

use App\Shared\Infrastructure\Http\Controllers\Controller;
use App\Tenant\GovernanceContext\LandingBuilderModule\Actions\GetOrCreateTenantLandingAction;
use App\Tenant\GovernanceContext\LandingBuilderModule\Services\LandingRendererService;
use Illuminate\Http\Request;

final class PublicLandingController extends Controller {
   public function __invoke(
      Request $request,
      GetOrCreateTenantLandingAction $getOrCreate,
      LandingRendererService $renderer,
   ) {
      $landing = $getOrCreate->execute();

      $isPreview = (
         $request->query('preview') === 'true'
         || $request->routeIs('tenant.landing.preview')
      ) && auth('tenant')->check();

      if ($landing->status !== 'published' && ! $isPreview) {
         abort(404);
      }

      $payload = $renderer->compile($landing);

      return view('landing-builder::public', $payload);
   }
}
