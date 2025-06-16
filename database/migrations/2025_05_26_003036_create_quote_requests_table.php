<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')
                ->nullable() // Podría ser nullable si QuoteRequest puede existir sin Lead al principio (ej. migración de datos antiguos)
                ->constrained('leads') // Referencia a la tabla 'leads'
                ->onDelete('set null');
            $table->string('vehicle_brand');
            $table->string('vehicle_model');
            $table->string('vehicle_version')->nullable();
            $table->string('vehicle_fuel')->nullable();
            $table->year('vehicle_year');
            $table->string('vehicle_postal_code');
            $table->string('dni')->nullable();
            $table->string('coverage_type')->nullable();
            $table->string('phone')->nullable();
            $table->string('session_id')->nullable();
            $table->boolean('quoted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
