<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de snapshots de métricas diarias por tenant.
 * Un Job nocturno la rellena; la UI solo lee.
 * Constraint UNIQUE (date, metric_key) garantiza idempotencia del Job.
 */
return new class extends Migration {
   public function up(): void {
      Schema::create('tenant_metric_snapshots', function (Blueprint $table): void {
         $table->id();
         $table->date('snapshot_date');
         $table->string('metric_key', 80);   // e.g. users_total, logins_day, files_uploaded
         $table->decimal('value', 15, 4)->default(0);
         $table->jsonb('meta')->default('{}'); // datos extra opcionales por métrica
         $table->timestamps();

         $table->unique(['snapshot_date', 'metric_key']);
         $table->index('snapshot_date');
         $table->index('metric_key');
      });
   }

   public function down(): void {
      Schema::dropIfExists('tenant_metric_snapshots');
   }
};
