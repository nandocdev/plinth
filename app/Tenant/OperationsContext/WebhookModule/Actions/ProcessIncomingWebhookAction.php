<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Actions;

use App\Tenant\OperationsContext\WebhookModule\Events\IncomingWebhookReceived;
use App\Tenant\OperationsContext\WebhookModule\Models\IncomingWebhookLog;
use App\Tenant\OperationsContext\WebhookModule\Models\IncomingWebhookToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class ProcessIncomingWebhookAction {
   public function execute(string $rawToken, Request $request): IncomingWebhookLog {
      /** @var IncomingWebhookToken|null $token */
      $token = IncomingWebhookToken::query()
         ->where('token', $rawToken)
         ->where('is_active', true)
         ->first();

      if ($token === null) {
         throw new NotFoundHttpException('Token de webhook inválido o revocado.');
      }

      // Filtra headers sensibles antes de persistir
      $safeHeaders = collect($request->headers->all())
         ->except(['authorization', 'cookie', 'x-forwarded-for'])
         ->map(static fn(array $v): string => implode(', ', $v))
         ->toArray();

      $log = DB::transaction(function () use ($token, $request, $safeHeaders): IncomingWebhookLog {
         $token->update(['last_used_at' => now()]);

         /** @var IncomingWebhookLog $log */
         $log = IncomingWebhookLog::query()->create([
            'tenant_incoming_webhook_token_id' => $token->id,
            'status' => IncomingWebhookLog::STATUS_RECEIVED,
            'source_ip' => $request->ip(),
            'headers' => $safeHeaders,
            'payload' => $request->all(),
            'response_status' => 200,
         ]);

         return $log;
      });

      event(new IncomingWebhookReceived($log->id, $token->id, $request->all()));

      return $log;
   }
}
