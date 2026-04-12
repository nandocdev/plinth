<?php

declare(strict_types=1);

namespace App\Shared\Support\Navigation;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;

final class AuthorizationChecker {
   public function passes(array $item, ?Authenticatable $user): bool {
      if (! isset($item['can'])) {
         return (bool) $user;
      }

      if (is_callable($item['can'])) {
         return (bool) ($item['can'])($user);
      }

      if (! $user instanceof Authorizable) {
         return false;
      }

      if (is_string($item['can'])) {
         return $user->can($item['can']);
      }

      if (is_array($item['can'])) {
         return $user->can($item['can']['ability'], $item['can']['model']);
      }

      return false;
   }
}
