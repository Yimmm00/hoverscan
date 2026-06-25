<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defect_records', function (Blueprint $table) {
            $table->id();
            $table->string('dataset_id')->unique(); // e.g., AST-XXXXXX
            $table->string('bridge_name');
            $table->string('defect_class');
            $table->string('severity')->default('Medium');
            $table->decimal('confidence_score', 5, 4);
            $table->string('image_path')->nullable();
            $table->integer('humidity');
            $table->integer('temperature');
            $table->json('bbox_coordinates')->nullable(); // Stores [x1, y1, x2, y2] spatial bounds arrays
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defect_records');
    }
};
