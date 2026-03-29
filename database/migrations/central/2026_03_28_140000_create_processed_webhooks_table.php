<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('processed_webhooks', function (Blueprint $table): void {
         $table->id();
         $table->string('provider', 40);
         $table->string('event_id', 191);
         $table->string('payload_hash', 64);
         $table->timestamp('processed_at');
         $table->timestamps();

         $table->unique(['provider', 'event_id']);
         $table->index(['provider', 'processed_at']);
      });
   }

   public function down(): void {
      Schema::dropIfExists('processed_webhooks');
   }
};
