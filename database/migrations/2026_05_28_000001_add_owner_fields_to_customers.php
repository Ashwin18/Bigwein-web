<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'owner_type')) {
                $table->enum('owner_type', ['seller', 'builder'])->nullable()->after('country');
            }
            if (!Schema::hasColumn('customers', 'company_name')) {
                $table->string('company_name')->nullable()->after('owner_type');
            }
            if (!Schema::hasColumn('customers', 'phone_alt')) {
                $table->string('phone_alt')->nullable()->after('mobile');
            }
        });
    }
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['owner_type', 'company_name', 'phone_alt']);
        });
    }
};
