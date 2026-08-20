<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('propertys', function (Blueprint $table) {
            if (!Schema::hasColumn('propertys', 'total_area'))       $table->decimal('total_area', 12, 2)->nullable()->after('price');
            if (!Schema::hasColumn('propertys', 'carpet_area'))      $table->decimal('carpet_area', 12, 2)->nullable()->after('total_area');
            if (!Schema::hasColumn('propertys', 'floor_number'))     $table->integer('floor_number')->nullable()->after('carpet_area');
            if (!Schema::hasColumn('propertys', 'total_floors'))     $table->integer('total_floors')->nullable()->after('floor_number');
            if (!Schema::hasColumn('propertys', 'age_of_building'))  $table->string('age_of_building')->nullable()->after('total_floors');
            if (!Schema::hasColumn('propertys', 'facing'))           $table->string('facing')->nullable()->after('age_of_building');
            if (!Schema::hasColumn('propertys', 'furnishing'))       $table->string('furnishing')->nullable()->after('facing');
            if (!Schema::hasColumn('propertys', 'water_supply'))     $table->string('water_supply')->nullable()->after('furnishing');
            if (!Schema::hasColumn('propertys', 'prop_status'))      $table->string('prop_status')->nullable()->after('water_supply');
            if (!Schema::hasColumn('propertys', 'maintenance'))      $table->decimal('maintenance', 12, 2)->nullable()->after('prop_status');
            if (!Schema::hasColumn('propertys', 'security_deposit')) $table->decimal('security_deposit', 12, 2)->nullable()->after('maintenance');
            if (!Schema::hasColumn('propertys', 'price_negotiable')) $table->tinyInteger('price_negotiable')->default(0)->after('security_deposit');
            if (!Schema::hasColumn('propertys', 'pincode'))          $table->string('pincode', 10)->nullable()->after('country');
        });
    }
    public function down(): void
    {
        Schema::table('propertys', function (Blueprint $table) {
            $table->dropColumn(['total_area','carpet_area','floor_number','total_floors',
                'age_of_building','facing','furnishing','water_supply','prop_status',
                'maintenance','security_deposit','price_negotiable','pincode']);
        });
    }
};
