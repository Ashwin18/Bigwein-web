<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers','aadhaar_number')) $table->string('aadhaar_number',12)->nullable()->index();
            if (!Schema::hasColumn('customers','kyc_status')) $table->string('kyc_status',20)->default('pending')->index();
            if (!Schema::hasColumn('customers','kyc_verified_at')) $table->timestamp('kyc_verified_at')->nullable();
            if (!Schema::hasColumn('customers','kyc_verified_by')) $table->unsignedBigInteger('kyc_verified_by')->nullable();
            if (!Schema::hasColumn('customers','kyc_reject_reason')) $table->text('kyc_reject_reason')->nullable();
        });

        if (!Schema::hasTable('customer_kyc')) {
            Schema::create('customer_kyc', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->string('aadhaar_number',12)->index();
                $table->string('aadhaar_front')->nullable();
                $table->string('aadhaar_back')->nullable();
                $table->string('status',20)->default('submitted')->index();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                $table->unique('customer_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_kyc');
        Schema::table('customers', function (Blueprint $table) {
            foreach (['aadhaar_number','kyc_status','kyc_verified_at','kyc_verified_by','kyc_reject_reason'] as $column) {
                if (Schema::hasColumn('customers',$column)) $table->dropColumn($column);
            }
        });
    }
};
