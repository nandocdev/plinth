<?php

declare(strict_types=1);

namespace App\Central\AffiliateModule\Policies;

use App\Central\AffiliateModule\Models\ReferralPartner;
use App\Central\AuthenticationModule\Models\User;

final class ReferralPartnerPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function create(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function update(User $user, ReferralPartner $partner): bool {
      return $user->email_verified_at !== null;
   }
}
