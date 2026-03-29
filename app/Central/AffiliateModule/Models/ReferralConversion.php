<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Models;

use Database\Factories\Central\AffiliateModule\ReferralConversionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReferralConversion extends Model {
   use HasFactory;

   public const STATUS_PENDING = 'pending';

   public const STATUS_QUALIFIED = 'qualified';

   public const STATUS_PAID = 'paid';

   public const STATUS_REJECTED = 'rejected';

   protected $table = 'referral_conversions';

   protected $fillable = [
      'referral_partner_id',
      'tenant_id',
      'referred_email',
      'status',
      'commission_cents',
      'currency',
      'converted_at',
      'metadata',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'commission_cents' => 'integer',
         'converted_at' => 'datetime',
         'metadata' => 'array',
      ];
   }

   /**
    * @return BelongsTo<ReferralPartner, $this>
    */
   public function partner(): BelongsTo {
      return $this->belongsTo(ReferralPartner::class, 'referral_partner_id');
   }

   protected static function newFactory(): ReferralConversionFactory {
      return ReferralConversionFactory::new();
   }
}
