<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Models;

use App\Central\TenantProvisioningModule\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

final class TenantSubscription extends Model {
   public const STATUS_TRIALING = 'trialing';

   public const STATUS_ACTIVE = 'active';

   public const STATUS_PAST_DUE = 'past_due';

   public const STATUS_CANCELED = 'canceled';

   public const STATUS_DELETED = 'deleted';

   /**
    * @var array<string, list<string>>
    */
   private const ALLOWED_TRANSITIONS = [
      self::STATUS_TRIALING => [self::STATUS_ACTIVE, self::STATUS_PAST_DUE, self::STATUS_CANCELED],
      self::STATUS_ACTIVE => [self::STATUS_PAST_DUE, self::STATUS_CANCELED],
      self::STATUS_PAST_DUE => [self::STATUS_ACTIVE, self::STATUS_CANCELED],
      self::STATUS_CANCELED => [self::STATUS_DELETED],
      self::STATUS_DELETED => [],
   ];

   protected $table = 'tenant_subscriptions';

   protected $fillable = [
      'tenant_id',
      'plan_id',
      'billing_period',
      'status',
      'trial_ends_at',
      'starts_at',
      'ends_at',
      'price_snapshot_cents',
      'external_id',
      'meta',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'trial_ends_at' => 'datetime',
         'starts_at' => 'datetime',
         'ends_at' => 'datetime',
         'meta' => 'array',
         'price_snapshot_cents' => 'integer',
      ];
   }

   public function tenant(): BelongsTo {
      return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
   }

   public function plan(): BelongsTo {
      return $this->belongsTo(Plan::class, 'plan_id');
   }

   /**
    * @return list<string>
    */
   public static function statuses(): array {
      return [
         self::STATUS_TRIALING,
         self::STATUS_ACTIVE,
         self::STATUS_PAST_DUE,
         self::STATUS_CANCELED,
         self::STATUS_DELETED,
      ];
   }

   public static function assertValidTransition(string $from, string $to): void {
      if ($from === $to) {
         return;
      }

      $allowedTargets = self::ALLOWED_TRANSITIONS[$from] ?? [];

      if (in_array($to, $allowedTargets, true)) {
         return;
      }

      throw new RuntimeException(sprintf('Transicion de suscripcion invalida: %s -> %s.', $from, $to));
   }
}
