<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('propertys', 'sub_type')) {
            Schema::table('propertys', fn (Blueprint $table) => $table->string('sub_type')->nullable()->after('propery_type'));
        }
        if (!Schema::hasColumn('propertys', 'listing_purpose')) {
            Schema::table('propertys', fn (Blueprint $table) => $table->string('listing_purpose')->nullable()->after('sub_type'));
        }
        if (!Schema::hasColumn('propertys', 'listing_type')) {
            Schema::table('propertys', fn (Blueprint $table) => $table->string('listing_type')->default('property')->after('listing_purpose'));
        }
        if (!Schema::hasColumn('propertys', 'business_type')) {
            Schema::table('propertys', fn (Blueprint $table) => $table->string('business_type')->nullable()->after('listing_type'));
        }
        if (!Schema::hasColumn('propertys', 'business_meta')) {
            Schema::table('propertys', fn (Blueprint $table) => $table->longText('business_meta')->nullable()->after('business_type'));
        }
        if (!Schema::hasColumn('propertys', 'reference_no')) {
            Schema::table('propertys', fn (Blueprint $table) => $table->string('reference_no')->nullable()->unique()->after('business_meta'));
        }
    }

    public function down(): void
    {
        foreach (['reference_no','business_meta','business_type','listing_type','listing_purpose','sub_type'] as $column) {
            if (Schema::hasColumn('propertys', $column)) {
                Schema::table('propertys', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }
};
