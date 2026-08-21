<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->index();
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('option_id')->nullable()->index();
            $table->text('value_text')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'group_id'], 'property_attribute_property_group_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_attribute_values');
    }
};
