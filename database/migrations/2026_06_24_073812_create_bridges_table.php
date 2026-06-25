<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bridges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('district')->default('Sarawak');
            $table->string('location_coords'); // Stores "latitude, longitude"
            $table->timestamp('last_inspection')->nullable();
            $table->integer('total_anomalies')->default(0);
            $table->timestamps(); // Generates created_at and updated_at automatically
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bridges');
    }
};
