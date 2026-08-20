<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('builder_project_details', function (Blueprint $table) {
            if (!Schema::hasColumn('builder_project_details', 'admin_remarks')) {
                $table->text('admin_remarks')->nullable()->after('nearby_places');
            }
            if (!Schema::hasColumn('builder_project_details', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('admin_remarks');
            }
            if (!Schema::hasColumn('builder_project_details', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_at');
            }
        });

        Schema::table('propertys', function (Blueprint $table) {
            if (!Schema::hasColumn('propertys', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->after('category_id')->index();
            }
            if (!Schema::hasColumn('propertys', 'project_unit_id')) {
                $table->unsignedBigInteger('project_unit_id')->nullable()->after('project_id')->index();
            }
            if (!Schema::hasColumn('propertys', 'tower')) {
                $table->string('tower', 80)->nullable()->after('project_unit_id');
            }
            if (!Schema::hasColumn('propertys', 'unit_number')) {
                $table->string('unit_number', 80)->nullable()->after('tower');
            }
        });

        Schema::create('builder_project_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('image');
            $table->string('image_type', 30)->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('builder_project_floor_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('title', 120);
            $table->string('file_name');
            $table->timestamps();
        });

        Schema::create('project_enquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('name', 120);
            $table->string('mobile', 30);
            $table->string('email', 160)->nullable();
            $table->string('configuration', 80)->nullable();
            $table->string('budget', 80)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_enquiries');
        Schema::dropIfExists('builder_project_floor_plans');
        Schema::dropIfExists('builder_project_images');

        Schema::table('propertys', function (Blueprint $table) {
            foreach (['project_id','project_unit_id','tower','unit_number'] as $column) {
                if (Schema::hasColumn('propertys', $column)) $table->dropColumn($column);
            }
        });

        Schema::table('builder_project_details', function (Blueprint $table) {
            foreach (['admin_remarks','submitted_at','approved_at'] as $column) {
                if (Schema::hasColumn('builder_project_details', $column)) $table->dropColumn($column);
            }
        });
    }
};