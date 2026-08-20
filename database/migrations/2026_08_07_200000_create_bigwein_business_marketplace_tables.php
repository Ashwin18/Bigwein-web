<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_categories', function (Blueprint $table) {
            $table->id(); $table->string('name',120); $table->string('slug',140)->unique();
            $table->boolean('status')->default(true); $table->integer('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('businesses', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('customer_id'); $table->unsignedBigInteger('business_category_id')->nullable();
            $table->string('reference_no',40)->nullable()->unique(); $table->string('title',180); $table->string('slug',220)->unique();
            $table->string('business_type',120)->nullable(); $table->string('business_status',40)->default('running');
            $table->year('established_year')->nullable(); $table->unsignedInteger('employees')->nullable();
            $table->text('description')->nullable(); $table->string('reason_for_sale',180)->nullable();
            $table->decimal('asking_price',15,2)->default(0); $table->boolean('negotiable')->default(false);
            $table->decimal('monthly_revenue',15,2)->nullable(); $table->decimal('monthly_expense',15,2)->nullable();
            $table->decimal('monthly_profit',15,2)->nullable(); $table->decimal('inventory_value',15,2)->nullable();
            $table->string('financial_visibility',30)->default('verified_buyers'); $table->string('premises_type',30)->nullable();
            $table->decimal('monthly_rent',15,2)->nullable(); $table->unsignedInteger('lease_months_remaining')->nullable();
            $table->decimal('built_up_area',12,2)->nullable(); $table->string('city',100)->nullable(); $table->string('state',100)->nullable();
            $table->string('locality',160)->nullable(); $table->text('address')->nullable(); $table->boolean('is_confidential')->default(false);
            $table->json('assets_included')->nullable(); $table->string('cover_image')->nullable();
            $table->string('request_status',30)->default('pending'); $table->boolean('status')->default(false);
            $table->boolean('is_featured')->default(false); $table->unsignedBigInteger('views')->default(0); $table->text('admin_remarks')->nullable();
            $table->timestamps(); $table->index(['customer_id','request_status']); $table->index(['business_category_id','city']);
        });
        Schema::create('business_images', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('business_id'); $table->string('image'); $table->integer('sort_order')->default(0);
            $table->timestamps(); $table->index('business_id');
        });
        Schema::create('business_documents', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('business_id'); $table->string('document_type',80); $table->string('file_name');
            $table->boolean('admin_only')->default(true); $table->timestamps(); $table->index('business_id');
        });
        Schema::create('business_enquiries', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('business_id'); $table->unsignedBigInteger('buyer_customer_id')->nullable();
            $table->string('name',120); $table->string('email',160)->nullable(); $table->string('mobile',30);
            $table->string('buyer_type',60)->nullable(); $table->string('investment_budget',80)->nullable();
            $table->text('message')->nullable(); $table->string('status',30)->default('new'); $table->timestamps(); $table->index(['business_id','status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('business_enquiries'); Schema::dropIfExists('business_documents');
        Schema::dropIfExists('business_images'); Schema::dropIfExists('businesses'); Schema::dropIfExists('business_categories');
    }
};