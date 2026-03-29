<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Response;

final class RecordCentralAuditTrail {
   /**
    * @param Closure(Request): Response $next
    */
   public function handle(Request $request, Closure $next): Response {
      $response = $next($request);

      /** @var \App\Central\AuthenticationModule\Models\User|null $user */
      $user = Auth::guard('central')->user();

      if ($user === null) {
         return $response;
      }

      [$event, $description, $properties] = $this->buildAuditPayload($request, $response->getStatusCode());

      activity('central_audit')
         ->causedBy($user)
         ->withProperties($properties)
         ->tap(function (Activity $activity) use ($event): void {
            $activity->event = $event;
         })
         ->log($description);

      return $response;
   }

   /**
    * @return array{0: string, 1: string, 2: array<string, mixed>}
    */
   private function buildAuditPayload(Request $request, int $statusCode): array {
      $routeName = (string) ($request->route()?->getName() ?? 'unknown');
      $method = strtoupper($request->method());

      $livewireAction = $this->resolveLivewireAction($request);
      $event = $livewireAction !== null ? 'livewire.action' : strtolower($method) . '.request';

      $description = $livewireAction !== null
         ? 'Livewire action: ' . $livewireAction
         : sprintf('HTTP %s %s', $method, $routeName);

      $tenantId = $this->resolveTenantId($request);

      $properties = [
         'route_name' => $routeName,
         'method' => $method,
         'path' => $request->path(),
         'status_code' => $statusCode,
         'ip' => $request->ip(),
         'tenant_id' => $tenantId,
         'livewire_action' => $livewireAction,
      ];

      if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch') || $request->isMethod('delete')) {
         $properties['payload'] = $this->sanitizePayload($request->all());
      }

      return [$event, $description, $properties];
   }

   private function resolveTenantId(Request $request): ?string {
      $candidate = $request->input('tenant_id')
         ?? $request->input('tenantId')
         ?? $request->route('tenant');

      if (is_scalar($candidate) && $candidate !== '') {
         return (string) $candidate;
      }

      return null;
   }

   private function resolveLivewireAction(Request $request): ?string {
      if (! $request->is('livewire/update')) {
         return null;
      }

      $components = $request->input('components');

      if (! is_array($components) || $components === []) {
         return null;
      }

      $first = $components[0] ?? null;

      if (! is_array($first)) {
         return null;
      }

      $calls = $first['calls'] ?? [];
      $method = is_array($calls) && isset($calls[0]['method']) ? (string) $calls[0]['method'] : null;

      $component = null;
      $snapshotRaw = $first['snapshot'] ?? null;
      if (is_string($snapshotRaw)) {
         $snapshot = json_decode($snapshotRaw, true);
         if (is_array($snapshot)) {
            $component = $snapshot['memo']['name'] ?? null;
         }
      }

      if ($method === null || $method === '') {
         return null;
      }

      return ($component !== null ? (string) $component : 'unknown-component') . '::' . $method;
   }

   /**
    * @param array<string, mixed> $payload
    * @return array<string, mixed>
    */
   private function sanitizePayload(array $payload): array {
      $sensitive = [
         'password',
         'password_confirmation',
         'current_password',
         'token',
         '_token',
         'two_factor_recovery_codes',
         'two_factor_secret',
      ];

      foreach ($sensitive as $field) {
         if (array_key_exists($field, $payload)) {
            $payload[$field] = '[REDACTED]';
         }
      }

      return $payload;
   }
}
