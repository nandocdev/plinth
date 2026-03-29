<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantInvoice extends Model {
   /** Siempre se lee/escribe en la BD central, incluso desde contexto tenant. */
   protected $connection = 'central';

   protected $table = 'tenant_invoices';

   public const STATUS_OPEN = 'open';

   public const STATUS_PAID = 'paid';

   public const STATUS_VOID = 'void';

   protected $fillable = [
      'tenant_id',
      'subscription_id',
      'invoice_number',
      'currency',
      'amount_cents',
      'status',
      'billing_period',
      'description',
      'paid_at',
      'meta',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'paid_at' => 'datetime',
         'amount_cents' => 'integer',
         'meta' => 'array',
      ];
   }

   public function subscription(): BelongsTo {
      return $this->belongsTo(TenantSubscription::class, 'subscription_id');
   }

   /**
    * @return list<string>
    */
   public static function statuses(): array {
      return [self::STATUS_OPEN, self::STATUS_PAID, self::STATUS_VOID];
   }
}
