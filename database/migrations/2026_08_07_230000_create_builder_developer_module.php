<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('builder_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->string('company_name', 180);
            $table->string('company_type', 60)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('gst_number', 30)->nullable();
            $table->string('cin_llpin', 40)->nullable();
            $table->string('rera_promoter_number', 80)->nullable();
            $table->text('registered_office_address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('website', 180)->nullable();
            $table->unsignedInteger('years_in_business')->nullable();
            $table->text('about_developer')->nullable();
            $table->string('logo')->nullable();
            $table->string('pan_document')->nullable();
            $table->string('gst_certificate')->nullable();
            $table->string('registration_certificate')->nullable();
            $table->string('rera_certificate')->nullable();
            $table->string('authorised_person_aadhaar')->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('admin_remarks')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->index(['status','customer_id']);
        });

        Schema::create('builder_project_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->unique();
            $table->string('project_segment', 40)->nullable();
            $table->string('project_subtype', 80)->nullable();
            $table->date('launch_date')->nullable();
            $table->date('possession_date')->nullable();
            $table->string('rera_number', 100)->nullable();
            $table->string('rera_url')->nullable();
            $table->string('rera_certificate')->nullable();
            $table->decimal('total_land_area', 14, 2)->nullable();
            $table->string('land_area_unit', 20)->default('sqft');
            $table->unsignedInteger('total_towers')->nullable();
            $table->unsignedInteger('total_blocks')->nullable();
            $table->unsignedInteger('total_floors')->nullable();
            $table->unsignedInteger('total_units')->nullable();
            $table->unsignedInteger('available_units')->nullable();
            $table->decimal('open_space_percent', 5, 2)->nullable();
            $table->json('amenities')->nullable();
            $table->json('specifications')->nullable();
            $table->json('nearby_places')->nullable();
            $table->timestamps();
        });

        Schema::create('builder_project_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('configuration', 80);
            $table->decimal('carpet_area', 12, 2)->nullable();
            $table->decimal('built_up_area', 12, 2)->nullable();
            $table->decimal('starting_price', 15, 2)->nullable();
            $table->decimal('maximum_price', 15, 2)->nullable();
            $table->unsignedInteger('available_units')->nullable();
            $table->timestamps();
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_project_units');
        Schema::dropIfExists('builder_project_details');
        Schema::dropIfExists('builder_profiles');
    }
};