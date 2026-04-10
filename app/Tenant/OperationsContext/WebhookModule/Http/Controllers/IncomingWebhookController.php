<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\WebhookModule\Http\Controllers;

use App\Tenant\OperationsContext\WebhookModule\Actions\ProcessIncomingWebhookAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class IncomingWebhookController extends Controller {
   public function __construct(
      private readonly ProcessIncomingWebhookAction $processor,
   ) {
   }

   /**
    * Endpoint público para recibir webhooks entrantes.
    * Sin auth de usuario: la autorización es el token en la URL.
    */
   public function receive(Request $request, string $token): JsonResponse {
      try {
         $log = $this->processor->execute($token, $request);

         return response()->json([
            'status' => 'accepted',
            'log_id' => $log->id,
         ], 200);
      } catch (NotFoundHttpException) {
         // Devuelve 404 genérico para no exponer información del sistema
         return response()->json(['status' => 'not_found'], 404);
      } catch (\Throwable) {
         return response()->json(['status' => 'error'], 500);
      }
   }
}
