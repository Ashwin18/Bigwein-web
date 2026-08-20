<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
return new class extends Migration {
 public function up(): void {
   $items=['Restaurant','Cafe / Bakery','Hotel / Hospitality','Retail Store','Supermarket','Salon / Spa','Gym / Fitness','Franchise','Manufacturing','Clinic / Healthcare','Pharmacy','Education','IT / Software Company','Digital Agency','Travel Agency','Automobile / Service Centre','E-commerce','Cloud Kitchen','Other'];
   foreach($items as $i=>$name) if(!DB::table('business_categories')->where('slug',Str::slug($name))->exists())
     DB::table('business_categories')->insert(['name'=>$name,'slug'=>Str::slug($name),'status'=>1,'sort_order'=>$i+1,'created_at'=>now(),'updated_at'=>now()]);
 }
 public function down(): void {}
};