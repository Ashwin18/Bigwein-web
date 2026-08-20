<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_amenity_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('amenity_id');
            $table->timestamps();

            $table->unique(['property_id', 'amenity_id']);
            $table->index('property_id');
            $table->index('amenity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_amenity_assignments');
    }
};
