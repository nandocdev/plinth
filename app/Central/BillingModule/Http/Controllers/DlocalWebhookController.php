<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Http\Controllers;

use App\Central\BillingModule\Actions\HandleDlocalWebhookAction;
use App\Central\BillingModule\Actions\NormalizeDlocalWebhookAction;
use App\Central\BillingModule\Actions\VerifyDlocalWebhookSignatureAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DlocalWebhookController {
   public function __invoke(
      Request $request,
      VerifyDlocalWebhookSignatureAction $verifySignature,
      NormalizeDlocalWebhookAction $normalizeWebhook,
      HandleDlocalWebhookAction $handleWebhook,
   ): JsonResponse {
      $rawPayload = (string) $request->getContent();
      $signature = $request->header('X-Dlocal-Signature')
         ?? $request->header('X-Webhook-Signature');

      if (! $verifySignature->execute($rawPayload, $signature)) {
         return response()->json([
            'processed' => false,
            'message' => 'Invalid dLocal signature.',
         ], 401);
      }

      $payload = $request->json()->all();
      if (! is_array($payload)) {
         $payload = [];
      }

      $data = $normalizeWebhook->execute($payload, $rawPayload);
      $result = $handleWebhook->execute($data);

      return response()->json($result);
   }
}
