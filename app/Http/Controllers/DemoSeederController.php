<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoSeederController extends Controller
{
    public function seed()
    {
        // Only allow once — skip if already seeded
        $count = DB::table('propertys')->where('meta_keywords','demo_seed')->count();
        if ($count > 0) {
            return response()->json(['message' => 'Demo data already exists! '.$count.' properties found.', 'status' => 'skip']);
        }

        $imgPath = public_path('images/property_title_img/');
        if (!file_exists($imgPath)) @mkdir($imgPath, 0755, true);

        // Download images from picsum.photos (free, no attribution needed)
        $imageIds = [10,20,29,39,48,56,65,75,84,96,106,116,125,135,145,154,164,174,180,190];
        $savedImages = [];
        foreach ($imageIds as $pid) {
            $filename = 'demo_prop_'.$pid.'.jpg';
            $filepath = $imgPath.$filename;
            if (!file_exists($filepath)) {
                $ctx = stream_context_create(['http'=>['timeout'=>10,'follow_location'=>true,'user_agent'=>'Mozilla/5.0']]);
                $img = @file_get_contents("https://picsum.photos/id/{$pid}/800/600", false, $ctx);
                if ($img) file_put_contents($filepath, $img);
                else $img = @file_get_contents("https://picsum.photos/800/600?random={$pid}", false, $ctx);
                if ($img && !file_exists($filepath)) file_put_contents($filepath, $img);
            }
            $savedImages[] = file_exists($filepath) ? $filename : null;
        }

        $now = now();
        $properties = [

            // ── FOR SALE — Villas / Houses (category_id=1) ───────────────
            [
                'category_id'=>1,'title'=>'3BHK Luxury Villa in Anna Nagar','slug_id'=>'3bhk-luxury-villa-anna-nagar-'.uniqid(),
                'description'=>'Stunning 3BHK luxury villa with modern interiors, spacious rooms, modular kitchen and a beautiful garden. Located in the heart of Anna Nagar with easy access to schools, hospitals and malls. Fully furnished with premium fittings.',
                'address'=>'12, 4th Street, Anna Nagar West, Chennai','client_address'=>'Anna Nagar, Chennai',
                'propery_type'=>0,'price'=>12000000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[0],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>1,'total_click'=>245,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished',
                'total_area'=>2200,'carpet_area'=>1850,'floor_number'=>0,'total_floors'=>2,'facing'=>'East',
                'latitude'=>13.0850,'longitude'=>80.2101,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>1,'title'=>'2BHK Premium Flat in Velachery','slug_id'=>'2bhk-premium-flat-velachery-'.uniqid(),
                'description'=>'Well-maintained 2BHK apartment on the 3rd floor with lift, covered parking and 24/7 security. Walking distance to Velachery MRTS station. Perfect for small families and IT professionals.',
                'address'=>'45, Vijayanagar, Velachery, Chennai','client_address'=>'Velachery, Chennai',
                'propery_type'=>0,'price'=>6500000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[1],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>189,'prop_status'=>'Ready to Move','furnishing'=>'Semi Furnished',
                'total_area'=>1150,'carpet_area'=>980,'floor_number'=>3,'total_floors'=>8,'facing'=>'North',
                'latitude'=>12.9766,'longitude'=>80.2209,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>1,'title'=>'4BHK Duplex Villa with Private Pool','slug_id'=>'4bhk-duplex-villa-ecr-'.uniqid(),
                'description'=>'Magnificent 4BHK duplex villa on ECR with a private swimming pool, home theatre, modular kitchen and landscaped garden. Perfect for large families who want luxury living by the beach.',
                'address'=>'78, ECR Road, Sholinganallur, Chennai','client_address'=>'ECR, Chennai',
                'propery_type'=>0,'price'=>32000000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[2],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>1,'total_click'=>312,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished',
                'total_area'=>4500,'carpet_area'=>3800,'floor_number'=>0,'total_floors'=>2,'facing'=>'South',
                'latitude'=>12.9023,'longitude'=>80.2275,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>3,'title'=>'3BHK Modern Townhouse in Perungudi','slug_id'=>'3bhk-townhouse-perungudi-'.uniqid(),
                'description'=>'Contemporary 3BHK townhouse in a gated community with clubhouse, swimming pool and gym. Close to OMR IT corridor. Excellent investment opportunity with high rental yield.',
                'address'=>'23, OMR Road, Perungudi, Chennai','client_address'=>'Perungudi, Chennai',
                'propery_type'=>0,'price'=>8500000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[3],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>156,'prop_status'=>'Under Construction','furnishing'=>'Unfurnished',
                'total_area'=>1800,'carpet_area'=>1550,'floor_number'=>1,'total_floors'=>3,'facing'=>'West',
                'latitude'=>12.9634,'longitude'=>80.2426,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>1,'title'=>'2BHK Ready to Move Home in Porur','slug_id'=>'2bhk-home-porur-'.uniqid(),
                'description'=>'Compact and cozy 2BHK house with car parking, garden area and rain water harvesting. Located in a quiet residential colony in Porur. DTCP approved, clear title documents.',
                'address'=>'9, Gandhi Nagar, Porur, Chennai','client_address'=>'Porur, Chennai',
                'propery_type'=>0,'price'=>4800000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[4],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>98,'prop_status'=>'Ready to Move','furnishing'=>'Unfurnished',
                'total_area'=>1050,'carpet_area'=>900,'floor_number'=>0,'total_floors'=>1,'facing'=>'North-East',
                'latitude'=>13.0334,'longitude'=>80.1557,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],

            // ── PLOTS (category_id=2) ─────────────────────────────────────
            [
                'category_id'=>2,'title'=>'1200 sqft Residential Plot in Tambaram','slug_id'=>'1200sqft-plot-tambaram-'.uniqid(),
                'description'=>'DTCP approved residential plot of 1200 sqft in a developing area of Tambaram. Corner plot with two road facing. All utilities available — water, electricity, sewage. Ready for immediate construction.',
                'address'=>'Survey No. 45, Tambaram East, Chennai','client_address'=>'Tambaram, Chennai',
                'propery_type'=>0,'price'=>2800000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[5],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>134,'prop_status'=>'Ready to Move','total_area'=>1200,
                'latitude'=>12.9260,'longitude'=>80.1280,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>2,'title'=>'2400 sqft Premium Plot in OMR','slug_id'=>'2400sqft-plot-omr-'.uniqid(),
                'description'=>'Prime 2400 sqft plot on OMR corridor. Surrounded by IT parks and residential projects. Perfect for building a dream home or investment. Walking distance to bus stop and metro station.',
                'address'=>'Plot No. 12, Sholinganallur, OMR, Chennai','client_address'=>'OMR, Chennai',
                'propery_type'=>0,'price'=>7500000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[6],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>1,'total_click'=>278,'prop_status'=>'Ready to Move','total_area'=>2400,
                'latitude'=>12.9022,'longitude'=>80.2273,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>2,'title'=>'800 sqft DTCP Plot in Avadi','slug_id'=>'800sqft-plot-avadi-'.uniqid(),
                'description'=>'Affordable 800 sqft DTCP approved plot in Avadi. Great connectivity to NH-16. Ideal for first-time buyers. Legal clear title. Panchayat water and electricity connections available.',
                'address'=>'DTCP Layout, Avadi, Chennai','client_address'=>'Avadi, Chennai',
                'propery_type'=>0,'price'=>1200000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[7],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>87,'prop_status'=>'Ready to Move','total_area'=>800,
                'latitude'=>13.1143,'longitude'=>80.0975,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>2,'title'=>'5000 sqft Agricultural Land in Trichy','slug_id'=>'5000sqft-farm-trichy-'.uniqid(),
                'description'=>'5000 sqft fertile agricultural land with well water facility. Excellent for farming, poultry or weekend getaway development. Easily accessible from Trichy-Chennai highway.',
                'address'=>'Village Road, Srirangam, Trichy','client_address'=>'Trichy, Tamil Nadu',
                'propery_type'=>0,'price'=>3200000,'city'=>'Trichy','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[8],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>62,'prop_status'=>'Ready to Move','total_area'=>5000,
                'latitude'=>10.8505,'longitude'=>78.6837,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],

            // ── FOR RENT — Houses (category_id=1, propery_type=1) ─────────
            [
                'category_id'=>1,'title'=>'2BHK Fully Furnished Flat for Rent','slug_id'=>'2bhk-rent-nungambakkam-'.uniqid(),
                'description'=>'Beautifully furnished 2BHK flat in premium Nungambakkam location. AC in all rooms, modular kitchen, washing machine, sofa and beds included. Ideal for corporate executives and young families.',
                'address'=>'15, Khader Nawaz Khan Road, Nungambakkam, Chennai','client_address'=>'Nungambakkam, Chennai',
                'propery_type'=>1,'price'=>28000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[9],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>1,'total_click'=>201,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished',
                'total_area'=>1100,'carpet_area'=>920,'floor_number'=>4,'total_floors'=>8,'facing'=>'North',
                'rentduration'=>'Monthly','security_deposit'=>56000,'maintenance'=>2500,
                'latitude'=>13.0569,'longitude'=>80.2425,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>1,'title'=>'1BHK Bachelor Apartment in T.Nagar','slug_id'=>'1bhk-rent-tnagar-'.uniqid(),
                'description'=>'Compact 1BHK apartment suitable for bachelors and working professionals. Close to T.Nagar shopping area and Panagal Park metro. Basic furnishings provided. No pet policy.',
                'address'=>'8, Usman Road, T.Nagar, Chennai','client_address'=>'T.Nagar, Chennai',
                'propery_type'=>1,'price'=>14000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[10],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>145,'prop_status'=>'Ready to Move','furnishing'=>'Semi Furnished',
                'total_area'=>600,'carpet_area'=>520,'floor_number'=>2,'total_floors'=>5,'facing'=>'East',
                'rentduration'=>'Monthly','security_deposit'=>28000,'maintenance'=>1500,
                'latitude'=>13.0418,'longitude'=>80.2341,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>3,'title'=>'3BHK Semi-Furnished House in Adyar','slug_id'=>'3bhk-rent-adyar-'.uniqid(),
                'description'=>'Spacious 3BHK independent house available for rent in prime Adyar location. Two-wheeler and car parking included. Close to Adyar market and Besant Nagar beach. Family preferred.',
                'address'=>'22, Gandhi Nagar, Adyar, Chennai','client_address'=>'Adyar, Chennai',
                'propery_type'=>1,'price'=>38000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[11],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>178,'prop_status'=>'Ready to Move','furnishing'=>'Semi Furnished',
                'total_area'=>1800,'carpet_area'=>1550,'floor_number'=>0,'total_floors'=>1,'facing'=>'South-East',
                'rentduration'=>'Monthly','security_deposit'=>76000,'maintenance'=>3000,
                'latitude'=>13.0067,'longitude'=>80.2517,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>1,'title'=>'2BHK Apartment near Sholinganallur IT Park','slug_id'=>'2bhk-rent-sholinganallur-'.uniqid(),
                'description'=>'Ready-to-move 2BHK apartment just 5 minutes walk from major IT parks on OMR. Lift, power backup, security. Ideal for IT professionals. Two-wheeler parking free, car parking extra.',
                'address'=>'Plot 5, Elcot SEZ Road, Sholinganallur, Chennai','client_address'=>'Sholinganallur, Chennai',
                'propery_type'=>1,'price'=>22000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[12],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>220,'prop_status'=>'Ready to Move','furnishing'=>'Semi Furnished',
                'total_area'=>1050,'carpet_area'=>890,'floor_number'=>6,'total_floors'=>10,'facing'=>'North-West',
                'rentduration'=>'Monthly','security_deposit'=>44000,'maintenance'=>2000,
                'latitude'=>12.9011,'longitude'=>80.2279,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],

            // ── PG / HOSTEL (category_id=5) ───────────────────────────────
            [
                'category_id'=>5,'title'=>'PG for Working Professionals — Anna Nagar','slug_id'=>'pg-anna-nagar-'.uniqid(),
                'description'=>'Premium PG accommodation for working professionals in Anna Nagar. Single and sharing rooms available. Includes breakfast and dinner, WiFi, AC, laundry, housekeeping. 24/7 security with CCTV.',
                'address'=>'56, 18th Main Road, Anna Nagar, Chennai','client_address'=>'Anna Nagar, Chennai',
                'propery_type'=>1,'price'=>9500,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[13],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>167,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished',
                'rentduration'=>'Monthly','security_deposit'=>9500,'maintenance'=>0,
                'latitude'=>13.0856,'longitude'=>80.2098,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>5,'title'=>'Girls PG with Home-cooked Meals — Velachery','slug_id'=>'girls-pg-velachery-'.uniqid(),
                'description'=>'Safe and homely PG accommodation exclusively for girls and women. Home-cooked vegetarian food, WiFi, power backup, washing machine. 5 mins from Velachery MRTS. Strict in-time policy.',
                'address'=>'12, 100 Feet Road, Velachery, Chennai','client_address'=>'Velachery, Chennai',
                'propery_type'=>1,'price'=>7500,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[14],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>134,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished',
                'rentduration'=>'Monthly','security_deposit'=>7500,'maintenance'=>0,
                'latitude'=>12.9768,'longitude'=>80.2207,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],

            // ── COMMERCIAL (category_id=4) ────────────────────────────────
            [
                'category_id'=>4,'title'=>'500 sqft Office Space in Nungambakkam','slug_id'=>'office-nungambakkam-'.uniqid(),
                'description'=>'Ready-to-use furnished office space in premium Nungambakkam business district. Conference room access, high-speed internet, 24/7 access, power backup, parking. Ideal for startups and SMEs.',
                'address'=>'3rd Floor, Sigma Towers, Nungambakkam, Chennai','client_address'=>'Nungambakkam, Chennai',
                'propery_type'=>1,'price'=>45000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[15],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>1,'total_click'=>89,'prop_status'=>'Ready to Move','furnishing'=>'Fully Furnished',
                'total_area'=>500,'carpet_area'=>450,'floor_number'=>3,'total_floors'=>10,'facing'=>'North',
                'rentduration'=>'Monthly','security_deposit'=>135000,'maintenance'=>5000,
                'latitude'=>13.0576,'longitude'=>80.2423,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
            [
                'category_id'=>4,'title'=>'Shop for Rent on Mount Road','slug_id'=>'shop-mount-road-'.uniqid(),
                'description'=>'Prime retail shop space on bustling Mount Road (Anna Salai). High foot traffic area, excellent visibility, suitable for retail, pharmacy, cafe or boutique. Ground floor corner unit.',
                'address'=>'Ground Floor, 234 Mount Road, Chennai','client_address'=>'Mount Road, Chennai',
                'propery_type'=>1,'price'=>65000,'city'=>'Chennai','state'=>'Tamil Nadu','country'=>'India',
                'title_image'=>$savedImages[16],'status'=>1,'request_status'=>'approved','post_type'=>0,
                'is_premium'=>0,'total_click'=>112,'prop_status'=>'Ready to Move','furnishing'=>'Unfurnished',
                'total_area'=>800,'carpet_area'=>720,'floor_number'=>0,'total_floors'=>5,'facing'=>'East',
                'rentduration'=>'Monthly','security_deposit'=>195000,'maintenance'=>8000,
                'latitude'=>13.0604,'longitude'=>80.2496,'added_by'=>1,'created_at'=>$now,'updated_at'=>$now,'meta_keywords'=>'demo_seed',
            ],
        ];

        // Insert all properties
        $insertedIds = [];
        foreach ($properties as $prop) {
            // Generate unique slug_id
            $prop['slug_id'] = $prop['slug_id'] ?? (strtolower(preg_replace('/[^a-z0-9]+/','-',trim($prop['title']))).'-'.substr(uniqid(),0,6));
            $id = DB::table('propertys')->insertGetId($prop);
            $insertedIds[] = $id;
        }

        // Add bedroom parameters for residential properties
        $bedroomParamId = DB::table('parameters')->where('name','Bedroom')->value('id') ?? 2;
        $bhkMap = [
            0 => 3, 1 => 2, 2 => 4, 3 => 3, 4 => 2,
            9 => 2, 10 => 1, 11 => 3, 12 => 2,
        ];
        foreach ($bhkMap as $propIdx => $bedrooms) {
            if (isset($insertedIds[$propIdx])) {
                DB::table('assign_parameters')->insert([
                    'modal_type'   => 'App\\Models\\Property',
                    'modal_id'     => $insertedIds[$propIdx],
                    'property_id'  => $insertedIds[$propIdx],
                    'parameter_id' => $bedroomParamId,
                    'value'        => $bedrooms,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }
        }

        return response()->json([
            'status'     => 'success',
            'message'    => '✅ Demo data seeded successfully!',
            'properties' => count($properties),
            'images'     => count(array_filter($savedImages)),
            'note'       => 'Visit /properties to see the listings. Delete this route from web.php after use.',
        ]);
    }

    public function clear()
    {
        $ids = DB::table('propertys')->where('meta_keywords','demo_seed')->pluck('id');
        DB::table('assign_parameters')->whereIn('property_id', $ids)->delete();
        DB::table('property_images')->whereIn('propertys_id', $ids)->delete();
        DB::table('propertys')->where('meta_keywords','demo_seed')->delete();
        return response()->json(['status'=>'success','message'=>'Demo properties removed.','removed'=>count($ids)]);
    }
}
