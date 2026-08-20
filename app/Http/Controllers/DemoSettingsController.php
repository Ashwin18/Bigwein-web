<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemoSettingsController extends Controller
{
    const DEMO_TAG = 'demo_seed';

    /** Check if demo mode is enabled */
    public static function isEnabled(): bool
    {
        return DB::table('settings')
            ->where('type', 'demo_mode_enabled')
            ->value('data') === '1';
    }

    /** Admin page */
    public function index()
    {
        $enabled = self::isEnabled();
        $demoCount = DB::table('propertys')
            ->where('meta_keywords', self::DEMO_TAG)
            ->where(function ($q) { $q->whereNull('listing_type')->orWhere('listing_type', 'property'); })
            ->count();
        $businessCount = DB::table('propertys')
            ->where('meta_keywords', self::DEMO_TAG)
            ->where('listing_type', 'business')
            ->count();
        $activeCount = DB::table('propertys')
            ->where('meta_keywords', self::DEMO_TAG)
            ->where(function ($q) { $q->whereNull('listing_type')->orWhere('listing_type', 'property'); })
            ->where('status', 1)->where('request_status', 'approved')->count();

        // Category breakdown of demo properties
        $breakdown = DB::table('propertys as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('p.meta_keywords', self::DEMO_TAG)
            ->where(function ($q) { $q->whereNull('p.listing_type')->orWhere('p.listing_type', 'property'); })
            ->select('c.category as cat_name', DB::raw('COUNT(*) as total'),
                     DB::raw('SUM(CASE WHEN p.propery_type=0 THEN 1 ELSE 0 END) as for_sale'),
                     DB::raw('SUM(CASE WHEN p.propery_type=1 THEN 1 ELSE 0 END) as for_rent'))
            ->groupBy('c.category')->get();

        // Demo projects count
        $projectCount = 0;
        $projectBreakdown = collect();
        try {
            $projectCount = DB::table('projects')->where('meta_keywords', self::DEMO_TAG)->count();
            $projectBreakdown = DB::table('projects as p')
                ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                ->where('p.meta_keywords', self::DEMO_TAG)
                ->select('c.category as cat_name', DB::raw('COUNT(*) as total'),
                         DB::raw('MAX(p.type) as project_type'))
                ->groupBy('c.category')->get();
        } catch (\Exception $e) {}

        return view('demo-settings.index', compact(
            'enabled', 'demoCount', 'businessCount', 'activeCount', 'breakdown',
            'projectCount', 'projectBreakdown'
        ));
    }

    /** Toggle demo mode ON/OFF */
    public function toggle(Request $request)
    {
        $enabled = $request->enabled ? '1' : '0';
        DB::table('settings')->updateOrInsert(
            ['type' => 'demo_mode_enabled'],
            ['data' => $enabled, 'updated_at' => now()]
        );
        return response()->json([
            'success' => true,
            'message' => $enabled === '1'
                ? 'Demo mode enabled — dashboard, frontend, approvals, notifications and analytics now use demo seed data only.'
                : 'Live mode enabled — dashboard, frontend, approvals, notifications and analytics now use real data only.',
        ]);
    }

    /** Seed demo properties */
    public function seed()
    {
        $now = now();

        // ── PROPERTIES: seed only if none exist ──────────────────
        $existingProps = DB::table('propertys')->where('meta_keywords', self::DEMO_TAG)
            ->where(function ($q) { $q->whereNull('listing_type')->orWhere('listing_type', 'property'); })->count();
        $propCount     = 0;

        if ($existingProps === 0) {
            $imgPath = public_path('images/property_title_img/');
            if (!file_exists($imgPath)) @mkdir($imgPath, 0755, true);
            $imageIds    = [10,20,29,39,48,56,65,75,84,96,106,116,125,135,145,154,164,174];
            $savedImages = [];
            foreach ($imageIds as $pid) {
                $filename = 'demo_prop_'.$pid.'.jpg';
                $filepath = $imgPath.$filename;
                if (!file_exists($filepath)) {
                    $ctx = stream_context_create(['http' => ['timeout' => 10, 'follow_location' => true, 'user_agent' => 'Mozilla/5.0 (compatible)']]);
                    $img = @file_get_contents("https://picsum.photos/id/{$pid}/800/600", false, $ctx);
                    if ($img) file_put_contents($filepath, $img);
                }
                $savedImages[] = file_exists($filepath) ? $filename : null;
            }

            $properties  = $this->getDemoProperties($savedImages, $now);
            $insertedIds = [];
            foreach ($properties as $prop) {
                $id = DB::table('propertys')->insertGetId($prop);
                $insertedIds[] = $id;
            }
            $propCount = count($properties);

            // Add bedroom parameters
            $bedroomParamId = DB::table('parameters')->where('name', 'Bedroom')->value('id') ?? 2;
            $bhkMap = [0=>3, 1=>2, 2=>4, 3=>3, 4=>2, 9=>2, 10=>1, 11=>3, 12=>2];
            foreach ($bhkMap as $idx => $beds) {
                if (isset($insertedIds[$idx])) {
                    DB::table('assign_parameters')->insert([
                        'modal_type'  => 'App\\Models\\Property',
                        'modal_id'    => $insertedIds[$idx],
                        'property_id' => $insertedIds[$idx],
                        'parameter_id'=> $bedroomParamId,
                        'value'       => $beds,
                        'created_at'  => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }

        // ── PROJECTS: seed only if none exist ───────────────────
        $existingProjects = 0;
        $projectCount     = 0;
        try {
            $existingProjects = DB::table('projects')->where('meta_keywords', self::DEMO_TAG)->count();
            if ($existingProjects === 0) {
                $pjImgPath = public_path('images/project_title_img/');
                if (!file_exists($pjImgPath)) @mkdir($pjImgPath, 0755, true);
                $pjImageIds = [10, 26, 43, 60, 76, 92];
                $pjSavedImages = [];
                foreach ($pjImageIds as $pid) {
                    $filename = 'demo_project_'.$pid.'.jpg';
                    $filepath = $pjImgPath.$filename;
                    if (!file_exists($filepath)) {
                        $ctx = stream_context_create(['http'=>['timeout'=>10,'follow_location'=>true,'user_agent'=>'Mozilla/5.0']]);
                        $img = @file_get_contents("https://picsum.photos/id/{$pid}/900/600", false, $ctx);
                        if ($img) file_put_contents($filepath, $img);
                    }
                    $pjSavedImages[] = file_exists($filepath) ? $filename : null; // just filename - accessor builds full URL
                }
                $demoProjects = $this->getDemoProjects($pjSavedImages, $now);
                foreach ($demoProjects as $proj) { DB::table('projects')->insert($proj); }
                $projectCount = count($demoProjects);
            }
        } catch (\Exception $e) { /* projects table may not exist */ }

        // Auto-enable demo mode
        DB::table('settings')->updateOrInsert(
            ['type' => 'demo_mode_enabled'],
            ['data' => '1', 'updated_at' => now()]
        );

        // Build response message
        $parts = [];
        if ($propCount > 0)        $parts[] = $propCount.' properties seeded';
        if ($projectCount > 0)     $parts[] = $projectCount.' projects seeded';
        if ($existingProps > 0)    $parts[] = $existingProps.' properties already existed (skipped)';
        if ($existingProjects > 0 && $projectCount === 0) $parts[] = $existingProjects.' projects already existed (skipped)';
        if (empty($parts))         $parts[] = 'All demo data already exists';

        return response()->json([
            'success' => true,
            'message' => implode(' · ', $parts).'. Demo mode enabled!',
        ]);
    }


    /** Clear all demo properties */
    public function clear()
    {
        $ids = DB::table('propertys')->where('meta_keywords', self::DEMO_TAG)->pluck('id');
        DB::table('assign_parameters')->whereIn('property_id', $ids)->delete();
        DB::table('property_images')->whereIn('propertys_id', $ids)->delete();
        DB::table('interested_users')->whereIn('property_id', $ids)->delete();
        DB::table('propertys')->where('meta_keywords', self::DEMO_TAG)->delete();

        // Auto-disable demo mode after clearing
        DB::table('settings')->updateOrInsert(
            ['type' => 'demo_mode_enabled'],
            ['data' => '0', 'updated_at' => now()]
        );

        // Clear demo projects too
        try {
            $pjIds = DB::table('projects')->where('meta_keywords', self::DEMO_TAG)->pluck('id');
            DB::table('project_documents')->whereIn('project_id', $pjIds)->delete();
            DB::table('project_plans')->whereIn('project_id', $pjIds)->delete();
            DB::table('projects')->where('meta_keywords', self::DEMO_TAG)->delete();
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => count($ids).' demo properties and demo projects removed.',
        ]);
    }

    /** Demo property data */
    private function getDemoProperties(array $imgs, $now): array
    {
        $tag = self::DEMO_TAG;
        return [
            ['category_id'=>1,'title'=>'3BHK Luxury Villa in Anna Nagar','description'=>'Stunning 3BHK luxury villa with modern interiors, spacious rooms, modular kitchen and a beautiful garden. Located in the heart of Anna Nagar with easy access to schools, hospitals and malls.','address'=>'12, 4th Street, Anna Nagar West, Chennai','client_address'=>'Anna Nagar, Chennai','propery_type'=>0,'price'=>12000000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[0]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>1,'total_click'=>245,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished','total_area'=>2200,'carpet_area'=>1850,'floor_number'=>0,'total_floors'=>2,'facing'=>'East','latitude'=>13.0850,'longitude'=>80.2101,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'3bhk-luxury-villa-anna-nagar-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>1,'title'=>'2BHK Premium Flat in Velachery','description'=>'Well-maintained 2BHK apartment on the 3rd floor with lift, covered parking and 24/7 security. Walking distance to Velachery MRTS station.','address'=>'45, Vijayanagar, Velachery, Chennai','client_address'=>'Velachery, Chennai','propery_type'=>0,'price'=>6500000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[1]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>189,'prop_status'=>'Ready to Move','furnishing'=>'Semi Furnished','total_area'=>1150,'carpet_area'=>980,'floor_number'=>3,'total_floors'=>8,'facing'=>'North','latitude'=>12.9766,'longitude'=>80.2209,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'2bhk-flat-velachery-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>1,'title'=>'4BHK Duplex Villa with Private Pool','description'=>'Magnificent 4BHK duplex villa on ECR with a private swimming pool, home theatre and landscaped garden. Perfect for large families.','address'=>'78, ECR Road, Sholinganallur, Chennai','client_address'=>'ECR, Chennai','propery_type'=>0,'price'=>32000000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[2]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>1,'total_click'=>312,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished','total_area'=>4500,'carpet_area'=>3800,'floor_number'=>0,'total_floors'=>2,'facing'=>'South','latitude'=>12.9023,'longitude'=>80.2275,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'4bhk-duplex-villa-ecr-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>3,'title'=>'3BHK Modern Townhouse in Perungudi','description'=>'Contemporary 3BHK townhouse in a gated community with clubhouse, swimming pool and gym. Close to OMR IT corridor.','address'=>'23, OMR Road, Perungudi, Chennai','client_address'=>'Perungudi, Chennai','propery_type'=>0,'price'=>8500000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[3]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>156,'prop_status'=>'Under Construction','furnishing'=>'Unfurnished','total_area'=>1800,'carpet_area'=>1550,'floor_number'=>1,'total_floors'=>3,'facing'=>'West','latitude'=>12.9634,'longitude'=>80.2426,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'3bhk-townhouse-perungudi-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>1,'title'=>'2BHK Ready to Move Home in Porur','description'=>'Compact and cozy 2BHK house with car parking, garden area and rain water harvesting. Located in a quiet residential colony.','address'=>'9, Gandhi Nagar, Porur, Chennai','client_address'=>'Porur, Chennai','propery_type'=>0,'price'=>4800000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[4]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>98,'prop_status'=>'Ready to Move','furnishing'=>'Unfurnished','total_area'=>1050,'carpet_area'=>900,'floor_number'=>0,'total_floors'=>1,'facing'=>'North-East','latitude'=>13.0334,'longitude'=>80.1557,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'2bhk-home-porur-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>2,'title'=>'1200 sqft Residential Plot in Tambaram','description'=>'DTCP approved residential plot of 1200 sqft in a developing area of Tambaram. Corner plot with two road facing. All utilities available.','address'=>'Survey No. 45, Tambaram East, Chennai','client_address'=>'Tambaram, Chennai','propery_type'=>0,'price'=>2800000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[5]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>134,'prop_status'=>'Ready to Move','total_area'=>1200,'latitude'=>12.9260,'longitude'=>80.1280,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'1200sqft-plot-tambaram-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>2,'title'=>'2400 sqft Premium Plot on OMR','description'=>'Prime 2400 sqft plot on OMR corridor. Surrounded by IT parks. Perfect for building a dream home or investment.','address'=>'Plot No. 12, Sholinganallur, OMR, Chennai','client_address'=>'OMR, Chennai','propery_type'=>0,'price'=>7500000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[6]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>1,'total_click'=>278,'prop_status'=>'Ready to Move','total_area'=>2400,'latitude'=>12.9022,'longitude'=>80.2273,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'2400sqft-plot-omr-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>2,'title'=>'800 sqft DTCP Approved Plot in Avadi','description'=>'Affordable 800 sqft DTCP approved plot in Avadi. Legal clear title. Panchayat water and electricity connections available.','address'=>'DTCP Layout, Avadi, Chennai','client_address'=>'Avadi, Chennai','propery_type'=>0,'price'=>1200000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[7]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>87,'prop_status'=>'Ready to Move','total_area'=>800,'latitude'=>13.1143,'longitude'=>80.0975,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'800sqft-plot-avadi-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>2,'title'=>'5000 sqft Agricultural Land in Trichy','description'=>'Fertile agricultural land with well water facility. Excellent for farming or weekend getaway development.','address'=>'Village Road, Srirangam, Trichy','client_address'=>'Trichy, Tamil Nadu','propery_type'=>0,'price'=>3200000,'city'=>'Trichy','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[8]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>62,'prop_status'=>'Ready to Move','total_area'=>5000,'latitude'=>10.8505,'longitude'=>78.6837,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'5000sqft-land-trichy-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>1,'title'=>'2BHK Fully Furnished Flat for Rent','description'=>'Beautifully furnished 2BHK flat in premium Nungambakkam. AC in all rooms, modular kitchen, washing machine included.','address'=>'15, Khader Nawaz Khan Road, Nungambakkam, Chennai','client_address'=>'Nungambakkam, Chennai','propery_type'=>1,'price'=>28000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[9]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>1,'total_click'=>201,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished','total_area'=>1100,'carpet_area'=>920,'floor_number'=>4,'total_floors'=>8,'rentduration'=>'Monthly','security_deposit'=>56000,'maintenance'=>2500,'latitude'=>13.0569,'longitude'=>80.2425,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'2bhk-rent-nungambakkam-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>1,'title'=>'1BHK Bachelor Flat in T.Nagar','description'=>'Compact 1BHK apartment for bachelors and working professionals. Close to T.Nagar shopping area and metro.','address'=>'8, Usman Road, T.Nagar, Chennai','client_address'=>'T.Nagar, Chennai','propery_type'=>1,'price'=>14000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[10]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>145,'prop_status'=>'Ready to Move','furnishing'=>'Semi Furnished','total_area'=>600,'carpet_area'=>520,'floor_number'=>2,'total_floors'=>5,'rentduration'=>'Monthly','security_deposit'=>28000,'maintenance'=>1500,'latitude'=>13.0418,'longitude'=>80.2341,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'1bhk-rent-tnagar-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>3,'title'=>'3BHK Semi-Furnished House in Adyar','description'=>'Spacious 3BHK independent house in prime Adyar. Parking included. Close to Besant Nagar beach. Family preferred.','address'=>'22, Gandhi Nagar, Adyar, Chennai','client_address'=>'Adyar, Chennai','propery_type'=>1,'price'=>38000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[11]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>178,'prop_status'=>'Ready to Move','furnishing'=>'Semi Furnished','total_area'=>1800,'carpet_area'=>1550,'floor_number'=>0,'total_floors'=>1,'rentduration'=>'Monthly','security_deposit'=>76000,'maintenance'=>3000,'latitude'=>13.0067,'longitude'=>80.2517,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'3bhk-rent-adyar-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>1,'title'=>'2BHK near Sholinganallur IT Park','description'=>'Ready-to-move 2BHK just 5 minutes walk from major IT parks on OMR. Lift, power backup, security.','address'=>'Plot 5, ELCOT SEZ Road, Sholinganallur, Chennai','client_address'=>'Sholinganallur, Chennai','propery_type'=>1,'price'=>22000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[12]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>220,'prop_status'=>'Ready to Move','furnishing'=>'Semi Furnished','total_area'=>1050,'carpet_area'=>890,'floor_number'=>6,'total_floors'=>10,'rentduration'=>'Monthly','security_deposit'=>44000,'maintenance'=>2000,'latitude'=>12.9011,'longitude'=>80.2279,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'2bhk-rent-sholinganallur-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>5,'title'=>'PG for Working Professionals — Anna Nagar','description'=>'Premium PG for working professionals. Single and sharing rooms. Includes breakfast and dinner, WiFi, AC, laundry, housekeeping.','address'=>'56, 18th Main Road, Anna Nagar, Chennai','client_address'=>'Anna Nagar, Chennai','propery_type'=>1,'price'=>9500,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[13]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>167,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished','rentduration'=>'Monthly','security_deposit'=>9500,'latitude'=>13.0856,'longitude'=>80.2098,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'pg-anna-nagar-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>5,'title'=>'Girls PG with Meals — Velachery','description'=>'Safe PG exclusively for girls. Home-cooked vegetarian food, WiFi, power backup, washing machine. 5 mins from Velachery MRTS.','address'=>'12, 100 Feet Road, Velachery, Chennai','client_address'=>'Velachery, Chennai','propery_type'=>1,'price'=>7500,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[14]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>134,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished','rentduration'=>'Monthly','security_deposit'=>7500,'latitude'=>12.9768,'longitude'=>80.2207,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'girls-pg-velachery-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>4,'title'=>'500 sqft Office Space in Nungambakkam','description'=>'Ready-to-use furnished office in premium Nungambakkam. Conference room, high-speed internet, 24/7 access, parking. Ideal for startups.','address'=>'3rd Floor, Sigma Towers, Nungambakkam, Chennai','client_address'=>'Nungambakkam, Chennai','propery_type'=>1,'price'=>45000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[15]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>1,'total_click'=>89,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished','total_area'=>500,'floor_number'=>3,'total_floors'=>10,'rentduration'=>'Monthly','security_deposit'=>135000,'maintenance'=>5000,'latitude'=>13.0576,'longitude'=>80.2423,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'office-nungambakkam-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
            ['category_id'=>4,'title'=>'Shop for Rent on Mount Road','description'=>'Prime retail shop on bustling Mount Road. High foot traffic, excellent visibility. Suitable for retail, pharmacy, cafe or boutique.','address'=>'Ground Floor, 234 Mount Road, Chennai','client_address'=>'Mount Road, Chennai','propery_type'=>1,'price'=>65000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','title_image'=>$imgs[16]??null,'status'=>1,'request_status'=>'approved','post_type'=>0,'is_premium'=>0,'total_click'=>112,'prop_status'=>'Ready to Move','furnishing'=>'Unfurnished','total_area'=>800,'floor_number'=>0,'total_floors'=>5,'rentduration'=>'Monthly','security_deposit'=>195000,'maintenance'=>8000,'latitude'=>13.0604,'longitude'=>80.2496,'added_by'=>1,'meta_keywords'=>$tag,'slug_id'=>'shop-mount-road-'.substr(uniqid(),0,6),'created_at'=>$now,'updated_at'=>$now],
        ];
    }

    /** Demo project data */
    private function getDemoProjects(array $imgs, $now): array
    {
        $tag = self::DEMO_TAG;
        return [
            ['title'=>'Green Valley Enclave','slug_id'=>'green-valley-enclave-'.substr(uniqid(),0,6),'category_id'=>1,'description'=>'Green Valley Enclave is a premium residential township spread across 12 acres in the heart of Anna Nagar. The project offers 2BHK and 3BHK apartments with world-class amenities including a clubhouse, swimming pool, gym, jogging track and landscaped gardens. All units are Vastu-compliant with excellent ventilation and natural light.','location'=>'Anna Nagar, Chennai','city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','type'=>'Ready to Move','image'=>$imgs[0]??null,'latitude'=>13.0850,'longitude'=>80.2101,'added_by'=>1,'is_admin_listing'=>1,'status'=>1,'request_status'=>'approved','total_click'=>245,'meta_keywords'=>$tag,'created_at'=>$now,'updated_at'=>$now],
            ['title'=>'Skyline Business Hub','slug_id'=>'skyline-business-hub-'.substr(uniqid(),0,6),'category_id'=>4,'description'=>'Skyline Business Hub is a Grade A commercial complex on the OMR IT Corridor offering premium office spaces, co-working areas and retail shops. The project features smart building systems, 100% power backup, multi-level parking, a food court and high-speed elevators. Ideal for startups, MNCs and retail businesses.','location'=>'OMR Road, Sholinganallur, Chennai','city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','type'=>'Under Construction','image'=>$imgs[1]??null,'latitude'=>12.9023,'longitude'=>80.2275,'added_by'=>1,'is_admin_listing'=>1,'status'=>1,'request_status'=>'approved','total_click'=>312,'meta_keywords'=>$tag,'created_at'=>$now,'updated_at'=>$now],
            ['title'=>'Royal Palm Residency','slug_id'=>'royal-palm-residency-'.substr(uniqid(),0,6),'category_id'=>1,'description'=>'Royal Palm Residency is a newly launched luxury residential project in Trichy offering 1BHK, 2BHK and 3BHK apartments at pre-launch prices. The project sits inside a gated community with 24/7 security, childrens play area, senior citizen corner, indoor games and an organic garden. Strategically located near Trichy airport and national highway.','location'=>'Thillai Nagar, Trichy','city'=>'Trichy','state'=>'Tamil Nadu','country'=>'India','type'=>'New Launch','image'=>$imgs[2]??null,'latitude'=>10.8050,'longitude'=>78.6856,'added_by'=>1,'is_admin_listing'=>1,'status'=>1,'request_status'=>'approved','total_click'=>178,'meta_keywords'=>$tag,'created_at'=>$now,'updated_at'=>$now],
            ['title'=>'Brigade Gardens Phase 2','slug_id'=>'brigade-gardens-phase2-'.substr(uniqid(),0,6),'category_id'=>1,'description'=>'Brigade Gardens Phase 2 is an extension of the successful Phase 1 project in Whitefield, Bangalore. Offering 3BHK and 4BHK premium villas with private gardens, this gated community features a clubhouse spanning 20,000 sq ft, tennis courts, swimming pool and a convenience store. Easy access to ITPL, KR Puram metro and Whitefield railway station.','location'=>'Whitefield, Bangalore','city'=>'Bangalore','state'=>'Karnataka','country'=>'India','type'=>'Under Construction','image'=>$imgs[3]??null,'latitude'=>12.9698,'longitude'=>77.7499,'added_by'=>1,'is_admin_listing'=>1,'status'=>1,'request_status'=>'approved','total_click'=>423,'meta_keywords'=>$tag,'created_at'=>$now,'updated_at'=>$now],
            ['title'=>'OMR Tech Park Plots','slug_id'=>'omr-tech-park-plots-'.substr(uniqid(),0,6),'category_id'=>2,'description'=>'Invest in DTCP-approved residential plots on the booming OMR Tech Corridor. Sizes range from 600 sqft to 2400 sqft with immediate registration. The layout has internal roads, drainage, water supply and electricity. Surrounded by IT companies, schools and hospitals, this is one of the best plot investments in Chennai.','location'=>'OMR Road, Perungudi, Chennai','city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India','type'=>'Ready to Move','image'=>$imgs[4]??null,'latitude'=>12.9634,'longitude'=>80.2426,'added_by'=>1,'is_admin_listing'=>1,'status'=>1,'request_status'=>'approved','total_click'=>156,'meta_keywords'=>$tag,'created_at'=>$now,'updated_at'=>$now],
            ['title'=>'Coimbatore Smart Township','slug_id'=>'coimbatore-smart-township-'.substr(uniqid(),0,6),'category_id'=>1,'description'=>'A futuristic integrated township in Coimbatore spanning 50 acres with residential apartments, villas, commercial spaces and a school within the complex. Smart home automation, EV charging stations, solar power and rainwater harvesting make this a truly sustainable community. Phase 1 apartments are now open for booking.','location'=>'Avinashi Road, Coimbatore','city'=>'Coimbatore','state'=>'Tamil Nadu','country'=>'India','type'=>'New Launch','image'=>$imgs[5]??null,'latitude'=>11.0168,'longitude'=>77.0491,'added_by'=>1,'is_admin_listing'=>1,'status'=>1,'request_status'=>'approved','total_click'=>98,'meta_keywords'=>$tag,'created_at'=>$now,'updated_at'=>$now],
        ];
    }

}
