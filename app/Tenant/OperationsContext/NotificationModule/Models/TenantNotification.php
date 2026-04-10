<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\NotificationModule\Models;

use Illuminate\Notifications\DatabaseNotification;

final class TenantNotification extends DatabaseNotification {
   protected $table = 'notifications';
}
