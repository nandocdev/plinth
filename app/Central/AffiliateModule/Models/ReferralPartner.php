<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Models;

use Database\Factories\Central\AffiliateModule\ReferralPartnerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ReferralPartner extends Model {
   use HasFactory;

   protected $table = 'referral_partners';

   protected $fillable = [
      'code',
      'name',
      'email',
      'payout_type',
      'payout_value',
      'is_active',
      'notes',
   ];

   /**
    * @return array<string, string>
    */
   protected function casts(): array {
      return [
         'payout_value' => 'decimal:2',
         'is_active' => 'boolean',
      ];
   }

   /**
    * @return HasMany<ReferralConversion, $this>
    */
   public function conversions(): HasMany {
      return $this->hasMany(ReferralConversion::class, 'referral_partner_id');
   }

   protected static function newFactory(): ReferralPartnerFactory {
      return ReferralPartnerFactory::new();
   }
}
