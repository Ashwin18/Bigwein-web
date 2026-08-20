<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Article;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Models\PropertysInquiry;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

        if (!has_permissions('read', 'dashboard')) {
            return redirect('dashboard')->with('error', PERMISSION_ERROR_MSG);
        } else {
            // 0:Sell 1:Rent 2:Sold 3:Rented
            $list['total_sell_property'] = Property::where('propery_type', '0')->get()->count();
            $list['total_rant_property'] = Property::where('propery_type', '1')->get()->count();

            $list['total_properties'] = Property::all()->count();
            $list['total_articles'] = Article::all()->count();
            $list['total_categories'] = Category::all()->count();
            $list['total_customer'] = Customer::all()->count();
            $list['recent_properties'] = Property::orderBy('id', 'DESC')->limit(10)->where('status', 1)->get();
            $today = now();

            /************************************************************************************ */
            // Get Month wise data
            $monthDates = array();
            for ($month = 1; $month <= 12; $month++) {
                $monthName = Carbon::create(null, $month, 1)->format('M');
                array_push($monthDates, "'" . $monthName . "'");
            }
            $propertiesQuery = Property::query()->whereYear('created_at', now()->year);

            // Calculate sell and rent counts
            $sellProperties = $propertiesQuery->clone()->where('propery_type', 0)->get();
            $rentProperties = $propertiesQuery->clone()->where('propery_type', 1)->get();

            // Create month series for sell and rent properties
            $sellMonthSeries = array_fill(0, 12, 0);
            $rentMonthSeries = array_fill(0, 12, 0);

            // Loop through sell properties and update month series
            foreach ($sellProperties as $property) {
                $monthIndex = Carbon::parse($property->created_at)->format('n') - 1; // Get the month index (0-11)
                $sellMonthSeries[$monthIndex]++;
            }

            // Loop through rent properties and update month series
            foreach ($rentProperties as $property) {
                $monthIndex = Carbon::parse($property->created_at)->format('n') - 1; // Get the month index (0-11)
                $rentMonthSeries[$monthIndex]++;
            }
            /************************************************************************************ */


            /************************************************************************************ */
            // Get Week wise data
            // Create an array to store the counts for each day of the week
            $sellWeekSeries = array_fill(1, 7, 0);
            $sellWeekPropertyCounts = Property::selectRaw('DAYOFWEEK(created_at) as day_of_week,COUNT(*) as count')->whereBetween('created_at', [now()->copy()->startOfWeek(), now()->copy()->endOfWeek()])->where('propery_type', 0)->groupBy(DB::raw('DAYOFWEEK(created_at)'))->get();
            foreach ($sellWeekPropertyCounts as $count) {
                $sellWeekSeries[$count->day_of_week] = $count->count;
            }

            $rentWeekPropertyCounts = Property::selectRaw('DAYOFWEEK(created_at) as day_of_week,COUNT(*) as count')->whereBetween('created_at', [now()->copy()->startOfWeek(), now()->copy()->endOfWeek()])->where('propery_type', 1)->groupBy(DB::raw('DAYOFWEEK(created_at)'))->get();
            // Create an array to store the counts for each day of the week
            $rentWeekSeries = array_fill(1, 7, 0);
            foreach ($rentWeekPropertyCounts as $count) {
                $rentWeekSeries[$count->day_of_week] = $count->count;
            }

            /************************************************************************************ */
            // Get day wise data
            $sellCountForDay = array_fill(1, 31, 0); // Initialize array for days 1 to 31
            $rentCountForDay = array_fill(1, 31, 0); // Initialize array for days 1 to 31

            // Get all properties
            $properties = $propertiesQuery->clone()->whereMonth('created_at', now()->month)->get();

            foreach ($properties as $property) {
                $day = Carbon::parse($property->created_at)->day; // Get the day of the month

                if ($property->getRawOriginal('propery_type') == 0) {
                    $sellCountForDay[$day]++;
                } elseif ($property->getRawOriginal('propery_type') == 1) {
                    $rentCountForDay[$day]++;
                }
            }

            $currentDates = range(1, 31); // Days of the month
            $sellCountForCurrentDay = array_values($sellCountForDay);
            $rentCountForCurrentDay = array_values($rentCountForDay);


            /************************************************************************************ */



            // Properties Data Query
            $properties = Property::select('id', 'category_id', 'title', 'price', 'title_image', 'latitude', 'longitude', 'city', 'total_click','propery_type')->with('category')->where('total_click', '>', 0)->orderBy('total_click', 'DESC')->limit(10)->get()->map(function($property){
                $property->property_type = ucfirst($property->propery_type);
                $property->promoted = $property->is_promoted;
                return $property;
            });

            // Get Category Data
            $getCategory = Category::withCount('properties')->get();
            $category_name = array();
            $category_count = array();
            foreach ($getCategory as $key => $value) {
                array_push($category_name, "`" . $value->category . "`");
                array_push($category_count, $value->properties_count);
            }

            // Prepare the chart data
            $chartData = [
                'sellmonthSeries' => $sellMonthSeries,
                'sellcountForCurrentDay' => $sellCountForCurrentDay,
                'rentcountForCurrentDay' => $rentCountForCurrentDay,
                'sellweekSeries' => $sellWeekSeries,
                'rentweekSeries' => $rentWeekSeries,
                'rentmonthSeries' => $rentMonthSeries,
                'weekDates' =>  [0 => "'Day1'", 1 => "'Day2'", 2 => "'Day3'", 3 => "'Day4'", 4 => "'Day5'", 5 => "'Day6'", 6 => "'Day7'"],
                'monthDates' =>  $monthDates,
                'currentDates' => $currentDates,
                'currentDate' => "[" . Carbon::now()->format('Y-m-d') . "]"

            ];

            $rows = array();
            $firebase_settings = array();



            $operate = '';

            $settings['company_name'] = system_setting('company_name');
            $settings['currency_symbol'] = system_setting('currency_symbol');



            $userData = Customer::select(DB::raw("COUNT(*) as count"))
                ->whereYear('created_at', date('Y'))
                ->groupBy(DB::raw("Month(created_at)"))
                ->pluck('count');


            // ── BIGWEIN V5.1 GLOBAL DATA MODE ───────────────────────────
            // One switch controls the entire visible dataset:
            // Demo ON  => demo_seed records ONLY
            // Demo OFF => live/non-demo records ONLY
            $demoModeEnabled = \App\Services\DataModeService::isDemo();
            $dataMode = \App\Services\DataModeService::mode();
            $dataModeLabel = \App\Services\DataModeService::label();

            $scopeProperty = function ($query, string $alias = 'propertys', bool $propertyOnly = true) {
                return \App\Services\DataModeService::applyPropertyScope($query, $alias, $propertyOnly);
            };

            // Mode inventory totals shown in the dashboard/status banner.
            $modePropertyQuery = \DB::table('propertys');
            $scopeProperty($modePropertyQuery, 'propertys', true);
            $modePropertyCount = $modePropertyQuery->count();

            $modeBusinessQuery = \DB::table('propertys')->where('listing_type', 'business');
            $scopeProperty($modeBusinessQuery, 'propertys', false);
            $modeBusinessCount = $modeBusinessQuery->count();

            $modeProjectCount = 0;
            try {
                $modeProjectQuery = \DB::table('projects');
                \App\Services\DataModeService::applyProjectScope($modeProjectQuery, 'projects');
                $modeProjectCount = $modeProjectQuery->count();
            } catch (\Throwable $e) {
                $modeProjectCount = 0;
            }

            // Main KPI property counts.
            $totalPropertiesQuery = \DB::table('propertys');
            $scopeProperty($totalPropertiesQuery);
            $totalProperties = $totalPropertiesQuery->count();

            $liveInventoryQuery = \DB::table('propertys')
                ->where('status', 1)
                ->where('request_status', 'approved');
            $scopeProperty($liveInventoryQuery);
            $liveInventory = $liveInventoryQuery->count();

            $newThisMonthQuery = \DB::table('propertys')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month);
            $scopeProperty($newThisMonthQuery);
            $newThisMonth = $newThisMonthQuery->count();

            // Approval queue is restricted to owner/customer submitted records within current mode.
            $pendingCountQuery = \DB::table('propertys')
                ->where('added_by', '!=', 0)
                ->where('request_status', 'pending');
            $scopeProperty($pendingCountQuery);
            $pendingCount = $pendingCountQuery->count();

            $approvedOwnerQuery = \DB::table('propertys')
                ->where('added_by', '!=', 0)
                ->where('request_status', 'approved');
            $scopeProperty($approvedOwnerQuery);
            $approvedOwnerListings = $approvedOwnerQuery->count();

            $rejectedOwnerQuery = \DB::table('propertys')
                ->where('added_by', '!=', 0)
                ->where('request_status', 'rejected');
            $scopeProperty($rejectedOwnerQuery);
            $rejectedOwnerListings = $rejectedOwnerQuery->count();

            $totalOwnerSubmissions = $pendingCount + $approvedOwnerListings + $rejectedOwnerListings;
            $approvalRate = $totalOwnerSubmissions > 0
                ? (int) round(($approvedOwnerListings / $totalOwnerSubmissions) * 100)
                : 0;
            $listingHealth = $totalProperties > 0
                ? (int) round(($liveInventory / $totalProperties) * 100)
                : 0;

            // Enquiries are always derived from properties in the active dataset.
            $totalEnquiryQuery = \DB::table('interested_users as iu')
                ->join('propertys as p', 'p.id', '=', 'iu.property_id');
            $scopeProperty($totalEnquiryQuery, 'p', false);
            $totalEnquiries = $totalEnquiryQuery->count('iu.id');

            $weeklyEnquiryQuery = \DB::table('interested_users as iu')
                ->join('propertys as p', 'p.id', '=', 'iu.property_id')
                ->where('iu.created_at', '>=', now()->subDays(7));
            $scopeProperty($weeklyEnquiryQuery, 'p', false);
            $enquiriesWeek = $weeklyEnquiryQuery->count('iu.id');

            // Owners: Demo mode uses owners attached to demo seeded listings.
            // Live mode uses genuine active seller/builder accounts and excludes demo-only ownership.
            $modeOwnerIds = \App\Services\DataModeService::ownerIdsForCurrentMode();
            if ($demoModeEnabled) {
                $ownerQuery = \DB::table('customers')->where('isActive', 1);
                $newOwnerQuery = \DB::table('customers')->where('isActive', 1)->whereDate('created_at', today());
                if (empty($modeOwnerIds)) {
                    $ownerQuery->whereRaw('1 = 0');
                    $newOwnerQuery->whereRaw('1 = 0');
                } else {
                    $ownerQuery->whereIn('id', $modeOwnerIds);
                    $newOwnerQuery->whereIn('id', $modeOwnerIds);
                }
            } else {
                $ownerQuery = \DB::table('customers')->where('isActive', 1)->whereIn('owner_type', ['seller', 'builder']);
                $newOwnerQuery = \DB::table('customers')->where('isActive', 1)->whereIn('owner_type', ['seller', 'builder'])->whereDate('created_at', today());
            }

            $stats = [
                'total_properties'        => $totalProperties,
                'live_inventory'          => $liveInventory,
                'total_owners'            => $ownerQuery->count(),
                'total_enquiries'         => $totalEnquiries,
                'new_this_month'          => $newThisMonth,
                'new_owners_today'        => $newOwnerQuery->count(),
                'enquiries_week'          => $enquiriesWeek,
                'approved_owner_listings' => $approvedOwnerListings,
                'rejected_owner_listings' => $rejectedOwnerListings,
                'listing_health'          => $listingHealth,
                'approval_rate'           => $approvalRate,
            ];

            // City inventory uses only the currently selected dataset.
            $cityStatsQuery = \DB::table('propertys')
                ->select(
                    'city',
                    \DB::raw('COUNT(*) as total'),
                    \DB::raw('SUM(CASE WHEN propery_type=0 THEN 1 ELSE 0 END) as for_sale'),
                    \DB::raw('SUM(CASE WHEN propery_type=1 THEN 1 ELSE 0 END) as for_rent')
                )
                ->where('status', 1)
                ->where('request_status', 'approved')
                ->whereNotNull('city')->where('city', '!=', '');
            $scopeProperty($cityStatsQuery);
            $cityStats = $cityStatsQuery->groupBy('city')->orderByDesc('total')->limit(6)->get();

            $currentMonthStart  = now()->copy()->startOfMonth()->toDateTimeString();
            $currentMonthEnd    = now()->copy()->endOfMonth()->toDateTimeString();
            $previousMonthStart = now()->copy()->subMonthNoOverflow()->startOfMonth()->toDateTimeString();
            $previousMonthEnd   = now()->copy()->subMonthNoOverflow()->endOfMonth()->toDateTimeString();

            $cityReportsQuery = \DB::table('propertys as p')
                ->select(
                    'p.city',
                    \DB::raw('COUNT(DISTINCT p.id) as total'),
                    \DB::raw('COUNT(DISTINCT CASE WHEN p.propery_type=0 THEN p.id END) as for_sale'),
                    \DB::raw('COUNT(DISTINCT CASE WHEN p.propery_type=1 THEN p.id END) as for_rent'),
                    \DB::raw('COUNT(DISTINCT CASE WHEN p.added_by != 0 THEN p.added_by END) as owners'),
                    \DB::raw('COUNT(DISTINCT iu.id) as enquiries'),
                    \DB::raw("COUNT(DISTINCT CASE WHEN p.created_at BETWEEN '{$currentMonthStart}' AND '{$currentMonthEnd}' THEN p.id END) as current_month"),
                    \DB::raw("COUNT(DISTINCT CASE WHEN p.created_at BETWEEN '{$previousMonthStart}' AND '{$previousMonthEnd}' THEN p.id END) as previous_month")
                )
                ->leftJoin('interested_users as iu', 'iu.property_id', '=', 'p.id')
                ->where('p.status', 1)
                ->where('p.request_status', 'approved')
                ->whereNotNull('p.city')->where('p.city', '!=', '');
            $scopeProperty($cityReportsQuery, 'p', true);
            $cityReports = $cityReportsQuery->groupBy('p.city')->orderByDesc('total')->limit(8)->get()
                ->map(function ($row) {
                    $current = (int) $row->current_month;
                    $previous = (int) $row->previous_month;
                    $row->trend = $previous > 0
                        ? (int) round((($current - $previous) / $previous) * 100)
                        : ($current > 0 ? 100 : 0);
                    return $row;
                });

            $catCountsQuery = \DB::table('propertys as p')
                ->join('categories as c', 'c.id', '=', 'p.category_id')
                ->select('c.id', 'c.category', \DB::raw('COUNT(*) as total'))
                ->where('p.status', 1)
                ->where('p.request_status', 'approved');
            $scopeProperty($catCountsQuery, 'p', true);
            $catCounts = $catCountsQuery->groupBy('c.id', 'c.category')->orderByDesc('total')->get();

            $subtypeQuery = \DB::table('propertys as p')
                ->join('categories as c', 'c.id', '=', 'p.category_id')
                ->select(
                    'c.id as category_id',
                    'c.category',
                    \DB::raw("CASE WHEN LOWER(c.category) LIKE '%commercial%' AND NULLIF(TRIM(p.commercial_type),'') IS NOT NULL THEN TRIM(p.commercial_type) ELSE TRIM(p.sub_type) END as subtype"),
                    \DB::raw('COUNT(*) as total')
                )
                ->where('p.status', 1)
                ->where('p.request_status', 'approved')
                ->where(function ($q) {
                    $q->where(function ($x) {
                        $x->whereNotNull('p.sub_type')->where('p.sub_type', '!=', '');
                    })->orWhere(function ($x) {
                        $x->whereNotNull('p.commercial_type')->where('p.commercial_type', '!=', '');
                    });
                });
            $scopeProperty($subtypeQuery, 'p', true);
            $subtypeCounts = $subtypeQuery
                ->groupBy('c.id', 'c.category', 'subtype')
                ->get()->filter(fn ($row) => !empty(trim((string) $row->subtype)))
                ->groupBy('category_id');

            $catStyleMap = [
                'Villa'      => ['icon'=>'bi bi-house',    'color'=>'#E5343A','subBg'=>'#FFF1F3','subColor'=>'#9F1239'],
                'Plot'       => ['icon'=>'bi bi-map',      'color'=>'#1D4ED8','subBg'=>'#EFF6FF','subColor'=>'#1E40AF'],
                'Townhouse'  => ['icon'=>'bi bi-houses',   'color'=>'#7C3AED','subBg'=>'#F5F3FF','subColor'=>'#6B21A8'],
                'Commercial' => ['icon'=>'bi bi-building', 'color'=>'#16A34A','subBg'=>'#F0FDF4','subColor'=>'#166534'],
                'PG House'   => ['icon'=>'bi bi-people',   'color'=>'#D97706','subBg'=>'#FFFBEB','subColor'=>'#92400E'],
            ];

            $categoryBreakdown = $catCounts->map(function ($c) use ($catStyleMap, $subtypeCounts) {
                $style = $catStyleMap[$c->category] ?? [
                    'icon'=>'bi bi-building','color'=>'#64748B','subBg'=>'#F3F4F6','subColor'=>'#374151'
                ];
                $subs = collect($subtypeCounts->get($c->id, []))->map(function ($row) use ($style) {
                    return [
                        'label' => $row->subtype,
                        'count' => (int) $row->total,
                        'bg'    => $style['subBg'],
                        'color' => $style['subColor'],
                    ];
                })->values()->toArray();
                return [
                    'name' => $c->category,
                    'total' => (int) $c->total,
                    'icon' => $style['icon'],
                    'color' => $style['color'],
                    'subs' => $subs,
                ];
            })->toArray();

            $pendingPropertiesQuery = \DB::table('propertys as p')
                ->leftJoin('customers as c', 'c.id', '=', 'p.added_by')
                ->where('p.added_by', '!=', 0)
                ->where('p.request_status', 'pending');
            $scopeProperty($pendingPropertiesQuery, 'p', true);
            $pendingProperties = $pendingPropertiesQuery
                ->select('p.*', 'c.name as owner_name')
                ->orderByDesc('p.created_at')->limit(10)->get();

            $citiesQuery = \DB::table('propertys')
                ->where('status', 1)
                ->where('request_status', 'approved')
                ->whereNotNull('city')->where('city', '!=', '');
            $scopeProperty($citiesQuery);
            $cities = $citiesQuery->distinct()->orderBy('city')->pluck('city');

            $categories = \App\Models\Category::where('status', 1)->get();

            return view('home', compact(
                'list','settings','properties','userData','chartData','currency_symbol','category_name','category_count',
                'stats','pendingCount','cityStats','cityReports','categoryBreakdown','pendingProperties','cities','categories',
                'demoModeEnabled','dataMode','dataModeLabel','modePropertyCount','modeProjectCount','modeBusinessCount'
            ));
        }
    }
    public function blank_dashboard()
    {


        return view('blank_home');
    }


    public function change_password()
    {

        return view('change_password.index');
    }
    public function changeprofile()
    {
        return view('change_profile.index');
    }

    public function check_password(Request $request)
    {
        $id = Auth::id();
        $oldpassword = $request->old_password;
        $user = DB::table('users')->where('id', $id)->first();


        $response['error'] = password_verify($oldpassword, $user->password) ? true : false;
        return response()->json($response);
    }



    public function store_password(Request $request)
    {

        $confPassword = $request->confPassword;
        $id = Auth::id();
        $role = Auth::user()->type;

        $users = User::find($id);

        if (isset($confPassword) && $confPassword != '') {
            $users->password = Hash::make($confPassword);
        }

        $users->update();
        return back()->with('success', 'Password Change Successfully');
    }

    public function update_profile(Request $request)
    {
        $request->validate([
        ]);
        try {
            $id = Auth::id();
            $role = Auth::user()->type;

            $users = User::find($id);
            if ($role == 0) {
                $users->name  = $request->name;
                $users->email  = $request->email;
            }

            if ($request->hasFile('profile_image')) {
                if(!empty($users->getRawOriginal('profile'))){
                    unlink_image($users->profile);
                }
                $users->profile = store_image($request->file('profile_image'), 'ADMIN_PROFILE_IMG_PATH');
            }
            $users->update();
            return back()->with('success', trans("Data Updated Successfully"));
        } catch (Exception $e) {
            return back()->with('error', trans("Something Went Wrong"));
        }
    }

    public function privacy_policy()
    {
        echo system_setting('privacy_policy');
    }


    public function firebase_messaging_settings(Request $request)
    {
        $file_path = public_path('firebase-messaging-sw.js');

        // Check if file exists
        if (File::exists($file_path)) {

            File::delete($file_path);
        }

        // Move new file
        $request->file->move(public_path(), 'firebase-messaging-sw.js');
    }
    public function getMapsData()
    {
        $apiKey = env('MAP_API_KEY');

        $url = "https://maps.googleapis.com/maps/api/js?" . http_build_query([
            'libraries' => 'places',
            'key' => $apiKey, // Use the API key from the .env file
            // Add any other parameters you need here
        ]);

        return file_get_contents($url);
    }
}
