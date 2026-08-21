<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_attribute_category_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('category_id')->index();
            $table->boolean('is_required')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['group_id', 'category_id'], 'property_attribute_group_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_attribute_category_map');
    }
};
