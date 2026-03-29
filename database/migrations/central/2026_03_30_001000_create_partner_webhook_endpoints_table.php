<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('partner_webhook_endpoints', function (Blueprint $table): void {
         $table->id();
         $table->string('name', 120);
         $table->string('target_url', 2048);
         $table->string('signing_secret', 255);
         $table->json('subscribed_events');
         $table->boolean('is_active')->default(true);
         $table->timestamps();

         $table->index('is_active');
      });

      DB::statement("ALTER TABLE partner_webhook_endpoints ADD CONSTRAINT partner_webhook_endpoints_name_unique UNIQUE (name)");
   }

   public function down(): void {
      Schema::dropIfExists('partner_webhook_endpoints');
   }
};
