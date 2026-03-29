<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Policies;

use App\Central\AffiliateModule\Models\ReferralConversion;
use App\Central\AuthenticationModule\Models\User;

final class ReferralConversionPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function update(User $user, ReferralConversion $conversion): bool {
      return $user->email_verified_at !== null;
   }
}
