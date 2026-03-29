<?php

declare(strict_types=1);

namespace App\Central\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CentralEventMailNotification extends Notification implements ShouldQueue {
   use Queueable;

   /**
    * @param array<int, string> $lines
    */
   public function __construct(
      private readonly string $subject,
      private readonly array $lines,
   ) {
      $this->onQueue('emails');
   }

   /**
    * @return array<int, string>
    */
   public function via(object $notifiable): array {
      return ['mail'];
   }

   public function toMail(object $notifiable): MailMessage {
      $message = (new MailMessage())
         ->subject($this->subject)
         ->greeting('Hola equipo central,');

      foreach ($this->lines as $line) {
         $message->line($line);
      }

      return $message->salutation('SaaS-Kit-2026');
   }
}
