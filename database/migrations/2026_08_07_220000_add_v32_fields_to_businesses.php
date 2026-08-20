<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'category_details')) {
                $table->json('category_details')->nullable()->after('assets_included');
            }
            if (!Schema::hasColumn('businesses', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('request_status');
            }
            if (!Schema::hasColumn('businesses', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'approved_at')) $table->dropColumn('approved_at');
            if (Schema::hasColumn('businesses', 'submitted_at')) $table->dropColumn('submitted_at');
            if (Schema::hasColumn('businesses', 'category_details')) $table->dropColumn('category_details');
        });
    }
};