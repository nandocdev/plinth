<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void {
      Schema::create('referral_partners', function (Blueprint $table): void {
         $table->id();
         $table->string('code', 40)->unique();
         $table->string('name', 120);
         $table->string('email', 190)->unique();
         $table->string('payout_type', 20)->default('percentage');
         $table->decimal('payout_value', 10, 2)->default(0);
         $table->boolean('is_active')->default(true)->index();
         $table->text('notes')->nullable();
         $table->timestamps();

         $table->index(['is_active', 'created_at']);
      });

      DB::connection('central')->statement('ALTER TABLE referral_partners DROP CONSTRAINT IF EXISTS referral_partners_payout_type_check');
      DB::connection('central')->statement("ALTER TABLE referral_partners ADD CONSTRAINT referral_partners_payout_type_check CHECK (payout_type IN ('fixed', 'percentage'))");
      DB::connection('central')->statement('ALTER TABLE referral_partners DROP CONSTRAINT IF EXISTS referral_partners_payout_value_check');
      DB::connection('central')->statement('ALTER TABLE referral_partners ADD CONSTRAINT referral_partners_payout_value_check CHECK (payout_value >= 0)');
   }

   public function down(): void {
      DB::connection('central')->statement('ALTER TABLE referral_partners DROP CONSTRAINT IF EXISTS referral_partners_payout_value_check');
      DB::connection('central')->statement('ALTER TABLE referral_partners DROP CONSTRAINT IF EXISTS referral_partners_payout_type_check');
      Schema::dropIfExists('referral_partners');
   }
};
