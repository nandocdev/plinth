<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\NotificationModule\Livewire\Forms;

use App\Tenant\OperationsContext\NotificationModule\DTOs\SendTenantNotificationData;
use Livewire\Form;

final class TenantNotificationComposerForm extends Form {
   public string $subject = '';
   public string $message = '';
   public string $targetRole = 'all';

   /**
    * @return array<string, list<string>>
    */
   public function rules(): array {
      return [
         'subject' => ['required', 'string', 'min:3', 'max:140'],
         'message' => ['required', 'string', 'min:3', 'max:2000'],
         'targetRole' => ['required', 'string', 'in:all,admin,manager,member'],
      ];
   }

   public function toData(): SendTenantNotificationData {
      return SendTenantNotificationData::fromArray([
         'subject' => $this->subject,
         'message' => $this->message,
         'targetRole' => $this->targetRole,
      ]);
   }

   public function resetCompose(): void {
      $this->subject = '';
      $this->message = '';
      $this->targetRole = 'all';
   }
}
