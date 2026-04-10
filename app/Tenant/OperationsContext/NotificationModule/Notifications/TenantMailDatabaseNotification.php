<?php

declare(strict_types=1);

namespace App\Tenant\OperationsContext\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TenantMailDatabaseNotification extends Notification implements ShouldQueue {
   use Queueable;

   public function __construct(
      private readonly string $subject,
      private readonly string $message,
      private readonly string $sentBy,
   ) {
      $this->onQueue('emails');
   }

   /**
    * @return array<int, string>
    */
   public function via(object $notifiable): array {
      return ['mail', 'database'];
   }

   public function toMail(object $notifiable): MailMessage {
      return (new MailMessage())
         ->subject($this->subject)
         ->greeting('Hola,')
         ->line($this->message)
         ->line('Enviado por: ' . $this->sentBy)
         ->salutation('SaaS-Kit-2026');
   }

   /**
    * @return array<string, mixed>
    */
   public function toArray(object $notifiable): array {
      return [
         'subject' => $this->subject,
         'message' => $this->message,
         'sent_by' => $this->sentBy,
         'sent_at' => now()->toIso8601String(),
      ];
   }
}
