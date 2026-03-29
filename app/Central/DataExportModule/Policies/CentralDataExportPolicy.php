<?php

declare(strict_types=1);

namespace App\Central\DataExportModule\Policies;

use App\Central\AuthenticationModule\Models\User;
use App\Central\DataExportModule\Models\CentralDataExport;

final class CentralDataExportPolicy {
   public function viewAny(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function create(User $user): bool {
      return $user->email_verified_at !== null;
   }

   public function view(User $user, CentralDataExport $export): bool {
      return $user->email_verified_at !== null;
   }

   public function download(User $user, CentralDataExport $export): bool {
      return $user->email_verified_at !== null && $export->status === CentralDataExport::STATUS_COMPLETED;
   }
}
