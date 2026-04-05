<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::connection('central')->create('referral_conversions', function (Blueprint $table): void {
         $table->id();
         $table->foreignId('referral_partner_id')->constrained('referral_partners')->cascadeOnDelete();
         $table->string('tenant_id', 64)->unique();
         $table->string('referred_email', 190)->nullable();
         $table->string('status', 20)->default('pending')->index();
         $table->unsignedInteger('commission_cents')->default(0);
         $table->char('currency', 3)->default('USD');
         $table->timestamp('converted_at')->nullable()->index();
         $table->jsonb('metadata')->default(json_encode([]));
         $table->timestamps();

         $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
         $table->index(['referral_partner_id', 'status']);
      });

      DB::connection('central')->statement('ALTER TABLE referral_conversions DROP CONSTRAINT IF EXISTS referral_conversions_status_check');
      DB::connection('central')->statement("ALTER TABLE referral_conversions ADD CONSTRAINT referral_conversions_status_check CHECK (status IN ('pending', 'qualified', 'paid', 'rejected'))");
   }

   public function down(): void {
      DB::connection('central')->statement('ALTER TABLE referral_conversions DROP CONSTRAINT IF EXISTS referral_conversions_status_check');
      Schema::connection('central')->dropIfExists('referral_conversions');
   }
};
