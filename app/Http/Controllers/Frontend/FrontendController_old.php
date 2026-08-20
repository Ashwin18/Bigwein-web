<?php
namespace App\Http\Controllers\Frontend;

use Exception;
use App\Models\Faq;
use App\Models\Slider;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Projects;
use App\Models\Property;
use App\Models\Setting;
use App\Models\CityImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FrontendController extends Controller
{
    // ── Base URL ──────────────────────────────────────────────────────────────
    const BASE = 'https://bigweinadmin.codegensolutions.com';

    // ── Format price in Indian notation ──────────────────────────────────────
    public static function fmt($amount): string
    {
        if (!$amount) return 'Price on Request';
        $n = (float)$amount;
        if ($n >= 10000000) return '₹ ' . number_format($n / 10000000, 2, '.', '') . ' Cr';
        if ($n >= 100000)   return '₹ ' . number_format($n / 100000, 0) . ' Lakhs';
        if ($n >= 1000)     return '₹ ' . number_format($n / 1000, 0) . 'K';
        return '₹ ' . number_format($n, 0);
    }

    // ── Get settings as key→value array (cached 1h) ───────────────────────────
    public static function settings(array $keys = []): array
    {
        return Cache::remember('bw_settings_' . md5(implode(',', $keys)), 3600, function () use ($keys) {
            $q = Setting::select('type', 'data');
            if ($keys) $q->whereIn('type', $keys);
            return $q->get()->pluck('data', 'type')->toArray();
        });
    }

    // ── Base approved property query ──────────────────────────────────────────
    /** Check demo mode — query DB directly */
    private function isDemoOn(): bool
    {
        static $demoOn = null;
        if ($demoOn === null) {
            $val = DB::table('settings')
                ->where('type', 'demo_mode_enabled')
                ->value('data');
            $demoOn = ($val === '1' || $val === 1);
        }
        return $demoOn;
    }

    /** Apply demo filter to any DB query builder */
    private function applyDemoFilter($q, string $table = 'propertys')
    {
        if (!$this->isDemoOn()) {
            $q->where(function($x) use ($table) {
                $x->where("{$table}.meta_keywords", '!=', 'demo_seed')
                  ->orWhereNull("{$table}.meta_keywords");
            });
        }
        return $q;
    }

    private function propQuery()
    {
        $q = Property::where('propertys.status', 1)
            ->where('propertys.request_status', 'approved');
        return $this->applyDemoFilter($q);
    }

    

    // ── Get param value from parameters array ─────────────────────────────────
    // parameters() accessor returns array of arrays: ['name'=>.., 'value'=>.., 'translated_name'=>..]
    private static function param(array $params, array $keywords): ?array
    {
        foreach ($params as $p) {
            $name = strtolower($p['name'] ?? '');
            foreach ($keywords as $kw) {
                if (str_contains($name, $kw)) return $p;
            }
        }
        return null;
    }

    // ── Check if property is favourited by current session user ───────────────
    private static function isFav(int $propId): bool
    {
        $u = session('bw_customer');
        if (!$u) return false;
        return DB::table('favourites')
            ->where('user_id', $u['id'])
            ->where('property_id', $propId)
            ->exists();
    }

    /* ========================================================================
       PAGE: HOME
    ======================================================================== */
    public function home()
    {
        // Featured properties
        $featured = $this->propQuery()->where('is_premium', 1)
            ->orderByDesc('total_click')->take(8)->get();
        if ($featured->isEmpty()) {
            $featured = $this->propQuery()->orderByDesc('created_at')->take(8)->get();
        }

        // Categories with property count via DB (avoids relationship name dependency)
        $categories = Category::where('status', 1)->with('translations')
            ->orderBy('sequence')->take(6)->get();
        $countQ = DB::table('propertys')
            ->select('category_id', DB::raw('COUNT(*) as cnt'))
            ->where('status', 1)->where('request_status', 'approved');
        $this->applyDemoFilter($countQ);
        $counts = $countQ->groupBy('category_id')->pluck('cnt', 'category_id');
        $categories->each(fn($c) => $c->property_count = $counts[$c->id] ?? 0);
        $categories = $categories->sortByDesc('property_count')->values();

        // Projects
        $projects = Projects::with(['category', 'translations'])
            ->where(['status' => 1, 'request_status' => 'approved'])
            ->orderByDesc('created_at')->take(8)->get();

        // Sliders
        $sliders = Slider::orderBy('sequence')->get();

        // FAQs
        $faqs = Faq::where('status', 1)->take(6)->get();

        // Settings
        $s = self::settings(['company_name','currency_symbol','web_logo','company_email',
            'company_tel1','facebook_id','instagram_id','twitter_id','youtube_id',
            'playstore_id','appstore_id','company_address','system_color']);

        $cities = CityImage::where('status', 1)->orderBy('city')
            ->get(['city'])->unique('city')->values();

        $customer = session('bw_customer');

        // ── Search widget data ──────────────────────────────
        $allCategories = Category::where('status', 1)->get();
        $searchCats = [
            'plot_id'       => $allCategories->firstWhere('category', 'Plot')?->id ?? 2,
            'pg_id'         => $allCategories->firstWhere('category', 'PG House')?->id ?? 5,
            'commercial_id' => $allCategories->firstWhere('category', 'Commercial')?->id ?? 4,
            'residential'   => $allCategories->filter(fn($c) => in_array($c->category, ['Villa','Townhouse','Apartment','Flat','House']))->pluck('id')->toArray(),
        ];
        $commercialTypes = ['Office', 'Co-working Space', 'Shop / Showroom', 'Warehouse', 'Factory / Industrial', 'Plot / Land'];

        // ── Dynamic search widget config from admin settings ──
        $swCfg = \App\Http\Controllers\SearchSettingsController::load();

        return view('frontend.home', compact(
            'featured','categories','projects','sliders','faqs','s','cities','customer','searchCats','commercialTypes','swCfg'
        ));
    }

    /* ========================================================================
       PAGE: PROPERTIES LISTING
    ======================================================================== */
    public function properties(Request $request)
    {
        $q = $this->propQuery();

        // ── Property type: support both ?type= (old) and ?propery_type= (new search) ──
        $ptype = $request->filled('propery_type') ? $request->propery_type : $request->type;
        if ($ptype !== null && $ptype !== '') {
            $q->where('propery_type', (int)$ptype);
        }

        // ── Text search ──
        if ($request->filled('search')) {
            $sr = $request->search;
            $q->where(fn($x) => $x->where('title','LIKE',"%$sr%")
                ->orWhere('city','LIKE',"%$sr%")
                ->orWhere('address','LIKE',"%$sr%"));
        }

        // ── Category ──
        if ($request->filled('category_id')) {
            $q->where('category_id', $request->category_id);
        }

        // ── City / Location (LIKE search) ──
        if ($request->filled('city')) {
            $city = $request->city;
            $q->where(function($qc) use ($city) {
                $qc->where('propertys.city',    'LIKE', "%$city%")
                   ->orWhere('propertys.state', 'LIKE', "%$city%")
                   ->orWhere('propertys.address','LIKE',"%$city%");
            });
        }

        // ── Budget ──
        if ($request->filled('min_price')) $q->where('price', '>=', (float)$request->min_price);
        if ($request->filled('max_price')) $q->where('price', '<=', (float)$request->max_price);

        // ── BHK type (e.g. "2 BHK,3 BHK") ──
        if ($request->filled('bhk')) {
            $bhkNums = array_filter(array_map(
                fn($b) => (int) filter_var(trim($b), FILTER_SANITIZE_NUMBER_INT),
                explode(',', $request->bhk)
            ));
            if (!empty($bhkNums)) {
                $propIds = DB::table('assign_parameters as ap')
                    ->join('parameters as p', 'p.id', '=', 'ap.parameter_id')
                    ->where('p.name', 'Bedroom')
                    ->whereIn('ap.value', $bhkNums)
                    ->pluck('ap.property_id');
                if ($propIds->isNotEmpty()) $q->whereIn('propertys.id', $propIds);
            }
        }

        // ── Property status ──
        if ($request->filled('prop_status')) {
            $q->where('prop_status', $request->prop_status);
        }

        // ── Sub type: PG Hostel / Flatmates ──
        if ($request->input('sub_type') === 'flatmates') {
            $q->where('rentduration', 'Monthly');
        }

        // ── Commercial types ──
        if ($request->filled('comm_types')) {
            $types = explode(',', $request->comm_types);
            $q->where(function($qc) use ($types) {
                foreach ($types as $t) {
                    $t = trim($t);
                    $qc->orWhere('propertys.title',       'LIKE', "%$t%")
                       ->orWhere('propertys.description', 'LIKE', "%$t%");
                }
            });
        }

        // ── Sort ──
        match ($request->input('sort','')) {
            'price_asc'  => $q->orderBy('price'),
            'price_desc' => $q->orderByDesc('price'),
            'newest'     => $q->orderByDesc('created_at'),
            default      => $q->orderByDesc('is_premium')->orderByDesc('total_click'),
        };

        $properties = $q->paginate(9)->withQueryString();
        $categories = Category::where('status',1)->with('translations')->get();
        $citiesQ = DB::table('propertys')->where('status',1)->where('request_status','approved')
            ->whereNotNull('city')->where('city','!=','');
        $this->applyDemoFilter($citiesQ);
        $cities = $citiesQ->distinct()->orderBy('city')->pluck('city');
        $s          = self::settings(['currency_symbol','web_logo','company_name']);
        $customer   = session('bw_customer');

        // Pass all active filters to view for filter tags display
        $filters = $request->only([
            'type','propery_type','search','city','min_price','max_price',
            'category_id','sort','bhk','prop_status','sub_type','comm_types'
        ]);
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        return view('frontend.properties.index', compact(
            'properties','categories','cities','s','filters','customer'
        ));
    }


    public function propertyDetail($slug)
    {
        $prop = $this->propQuery()
            ->when(is_numeric($slug), fn($q) => $q->where('propertys.id', $slug))
            ->when(!is_numeric($slug), fn($q) => $q->where('propertys.slug_id', $slug))
            ->first();

        if (!$prop) abort(404);

        // Increment view count
        DB::table('propertys')->where('id', $prop->id)->increment('total_click');

        // ── Owner / Poster details ──────────────────────────────
        $owner = null;
        if ($prop->added_by) {
            $owner = DB::table('customers')->where('id', $prop->added_by)
                ->select('id','name','mobile','country_code','profile','owner_type','company_name','city','isActive')
                ->first();
        }

        // ── Gallery images ──────────────────────────────────────
        $gallery = DB::table('property_images')
            ->where('propertys_id', $prop->id)
            ->get();

        // ── Parameters (bedrooms, bathrooms etc.) ───────────────
        $parameters = DB::table('assign_parameters as ap')
            ->join('parameters as par', 'par.id', '=', 'ap.parameter_id')
            ->where('ap.property_id', $prop->id)
            ->select('par.name', 'par.image', 'ap.value')
            ->get();

        // ── Nearby facilities ───────────────────────────────────
        $facilities = DB::table('assigned_outdoor_facilities as af')
            ->join('outdoor_facilities as of', 'of.id', '=', 'af.facility_id')
            ->where('af.property_id', $prop->id)
            ->select('of.name', 'of.image', 'af.distance')
            ->get();

        // ── Category name ───────────────────────────────────────
        $category = DB::table('categories')->where('id', $prop->category_id)->value('category');

        // ── Has the current buyer already enquired? ─────────────
        $customer    = session('bw_customer');
        $hasEnquired = false;
        if ($customer) {
            $hasEnquired = DB::table('interested_users')
                ->where('customer_id', $customer['id'])
                ->where('property_id', $prop->id)
                ->exists();
        }

        // ── Similar properties (same category, fallback to same type) ──
        $similar = $this->propQuery()
            ->where('propertys.category_id', $prop->category_id)
            ->where('propertys.id', '!=', $prop->id)
            ->inRandomOrder()->take(4)->get();

        // Fallback: if not enough same-category results, fill with same propery_type
        if ($similar->count() < 4) {
            $exclude = $similar->pluck('id')->push($prop->id)->toArray();
            $fallback = $this->propQuery()
                ->where('propertys.propery_type', $prop->propery_type)
                ->whereNotIn('propertys.id', $exclude)
                ->inRandomOrder()->take(4 - $similar->count())->get();
            $similar = $similar->concat($fallback);
        }

        // Final fallback: just get any 4 latest properties
        if ($similar->count() < 4) {
            $exclude = $similar->pluck('id')->push($prop->id)->toArray();
            $fallback = $this->propQuery()
                ->whereNotIn('propertys.id', $exclude)
                ->inRandomOrder()->take(4 - $similar->count())->get();
            $similar = $similar->concat($fallback);
        }

        $isFav = self::isFav($prop->id);
        $s     = self::settings(['currency_symbol','web_logo','company_name']);

        return view('frontend.properties.show', compact(
            'prop','owner','gallery','parameters','facilities',
            'category','similar','isFav','s','customer','hasEnquired'
        ));
    }

    /* ========================================================================
       PAGE: PROJECTS
    ======================================================================== */
    public function projects(Request $request)
    {
        $q = Projects::with(['category','translations'])
            ->where(['status'=>1,'request_status'=>'approved']);

        // Filters
        if ($request->filled('type'))     $q->where('type', $request->type);
        if ($request->filled('city'))     $q->where('city', $request->city);
        if ($request->filled('cat'))      $q->where('category_id', $request->cat);

        // Sort
        $sort = $request->get('sort','latest');
        match($sort) {
            'popular' => $q->orderByDesc('total_click'),
            'oldest'  => $q->orderBy('created_at'),
            default   => $q->orderByDesc('created_at'),
        };

        $projects = $q->paginate(9)->withQueryString();

        // Stats for hero banner
        $baseQ    = Projects::where(['status'=>1,'request_status'=>'approved']);
        $stats = [
            'total'        => (clone $baseQ)->count(),
            'ready'        => (clone $baseQ)->where('type','Ready to Move')->count(),
            'construction' => (clone $baseQ)->where('type','Under Construction')->count(),
            'new_launch'   => (clone $baseQ)->where('type','New Launch')->count(),
            'cities'       => (clone $baseQ)->whereNotNull('city')->where('city','!=','')->distinct('city')->count('city'),
        ];

        // Filter options
        $cities     = Projects::where(['status'=>1,'request_status'=>'approved'])
                        ->whereNotNull('city')->where('city','!=','')
                        ->distinct()->orderBy('city')->pluck('city');
        $categories = \App\Models\Category::where('status',1)->orderBy('sequence')->get();

        $s        = self::settings(['currency_symbol','web_logo','company_name']);
        $customer = session('bw_customer');

        return view('frontend.projects.index', compact(
            'projects','s','customer','stats','cities','categories'
        ));
    }

    /* ========================================================================
       PAGE: PROJECT DETAIL
    ======================================================================== */
    public function projectDetail(Request $request, $slug)
    {
        $project = \App\Models\Projects::with(['category','translations','gallary_images','plans','project_documetns'])
            ->where('slug_id', $slug)->where('status',1)->where('request_status','approved')
            ->firstOrFail();

        // Increment views
        \DB::table('projects')->where('id',$project->id)->increment('total_click');

        // Similar projects (same category, exclude current)
        $similar = \App\Models\Projects::where('category_id',$project->category_id)
            ->where('id','!=',$project->id)->where(['status'=>1,'request_status'=>'approved'])
            ->inRandomOrder()->take(3)->get();

        if ($similar->count() < 3) {
            $more = \App\Models\Projects::where('id','!=',$project->id)
                ->whereNotIn('id',$similar->pluck('id'))
                ->where(['status'=>1,'request_status'=>'approved'])
                ->inRandomOrder()->take(3 - $similar->count())->get();
            $similar = $similar->concat($more);
        }

        $s        = self::settings(['currency_symbol','web_logo','company_name','company_tel1','company_email']);
        $customer = session('bw_customer');

        return view('frontend.projects.show', compact('project','similar','s','customer'));
    }

    /* ========================================================================
       PAGE: AGENTS
    ======================================================================== */
    public function agents(Request $request)
    {
        $q = Customer::where('isActive', 1)
            ->where('logintype', '!=', '0'); // exclude admin accounts

        if ($request->filled('q')) {
            $search = $request->q;
            $q->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
            });
        }
        if ($request->filled('city')) {
            $q->where('city', $request->city);
        }

        $agents = $q->orderByDesc('created_at')->paginate(12)->withQueryString();

        $cities = Customer::where('isActive', 1)
            ->whereNotNull('city')->where('city', '!=', '')
            ->distinct()->orderBy('city')->pluck('city');

        $s        = self::settings(['web_logo','company_name']);
        $customer = session('bw_customer');
        return view('frontend.agents.index', compact('agents','cities','s','customer'));
    }

    /* ========================================================================
       JSON: SLIDERS
    ======================================================================== */
    public function apiSliders()
    {
        try {
            $sliders = Cache::remember('bw_sliders', 1800, fn() => Slider::orderBy('sequence')->get());
            return response()->json(['error' => false, 'data' => $sliders]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'data' => []]);
        }
    }

    /* ========================================================================
       JSON: PROPERTIES
    ======================================================================== */
    public function apiProperties(Request $request)
    {
        try {
            $q = $this->propQuery();
            if ($request->filled('type'))        $q->where('propery_type', $request->type);
            if ($request->filled('category_id')) $q->where('category_id', $request->category_id);

        // ── Advanced search filters ──────────────────────────
        // BHK filter (bedroom count from assign_parameters)
        if ($request->filled('bhk')) {
            $bhkVals = explode(',', $request->bhk);
            $bhkNums = array_map(fn($b) => (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT), $bhkVals);
            $propIds = DB::table('assign_parameters as ap')
                ->join('parameters as p', 'p.id', '=', 'ap.parameter_id')
                ->where('p.name', 'Bedroom')
                ->whereIn('ap.value', $bhkNums)
                ->pluck('ap.property_id');
            if ($propIds->isNotEmpty()) $q->whereIn('propertys.id', $propIds);
        }

        // Property status
        if ($request->filled('prop_status')) {
            $q->where('prop_status', $request->prop_status);
        }

        // Sub-type (flatmates special handling)
        if ($request->input('sub_type') === 'flatmates') {
            $q->where('rentduration', 'Monthly');
        }

        // City search
        if ($request->filled('city')) {
            $q->where(function($qc) use ($request) {
                $qc->where('propertys.city', 'LIKE', '%'.$request->city.'%')
                   ->orWhere('propertys.address', 'LIKE', '%'.$request->city.'%')
                   ->orWhere('propertys.state', 'LIKE', '%'.$request->city.'%');
            });
        }

        // Commercial types (stored in about_me / description filter)
        if ($request->filled('comm_types')) {
            $types = explode(',', $request->comm_types);
            $q->where(function($qc) use ($types) {
                foreach ($types as $type) {
                    $qc->orWhere('propertys.description', 'LIKE', '%'.$type.'%')
                       ->orWhere('propertys.title', 'LIKE', '%'.$type.'%');
                }
            });
        }
            if ($request->filled('city'))        $q->where('city', $request->city);
            if ($request->filled('min_price'))   $q->where('price', '>=', $request->min_price);
            if ($request->filled('max_price'))   $q->where('price', '<=', $request->max_price);
            if ($request->filled('search')) {
                $sr = $request->search;
                $q->where(fn($x) => $x->where('title','LIKE',"%$sr%")->orWhere('city','LIKE',"%$sr%"));
            }
            $total = $q->count();
            $data  = $q->orderByDesc('is_premium')->orderByDesc('total_click')
                ->skip((int)$request->input('offset',0))
                ->take((int)$request->input('limit',9))
                ->get();
            return response()->json(['error'=>false,'data'=>$data,'total'=>$total]);
        } catch (Exception $e) {
            return response()->json(['error'=>true,'message'=>$e->getMessage()]);
        }
    }

    /* ========================================================================
       JSON: PROPERTY DETAIL
    ======================================================================== */
    public function apiPropertyDetail($id)
    {
        try {
            $prop = $this->propQuery()
                ->when(is_numeric($id), fn($q) => $q->where('id',$id))
                ->when(!is_numeric($id), fn($q) => $q->where('slug_id',$id))
                ->firstOrFail();
            return response()->json(['error'=>false,'data'=>[$prop]]);
        } catch (Exception $e) {
            return response()->json(['error'=>true,'message'=>'Not found'],404);
        }
    }

    /* ========================================================================
       JSON: PROJECTS
    ======================================================================== */
    public function apiProjects(Request $request)
    {
        try {
            $q = Projects::with(['category','translations'])
                ->where(['status'=>1,'request_status'=>'approved']);
            if ($request->filled('type')) $q->where('type', $request->type);
            $total = $q->count();
            $data  = $q->orderByDesc('created_at')
                ->skip((int)$request->input('offset',0))
                ->take((int)$request->input('limit',9))
                ->get();
            return response()->json(['error'=>false,'data'=>$data,'total'=>$total]);
        } catch (Exception $e) {
            return response()->json(['error'=>true,'data'=>[]]);
        }
    }

    /* ========================================================================
       JSON: CATEGORIES
    ======================================================================== */
    public function apiCategories()
    {
        try {
            $cats = Cache::remember('bw_categories', 3600, function () {
                $cats   = Category::where('status',1)->with('translations')->get();
                $counts = DB::table('propertys')
                    ->select('category_id', DB::raw('COUNT(*) as cnt'))
                    ->where(['status'=>1,'request_status'=>'approved'])
                    ->groupBy('category_id')->pluck('cnt','category_id');
                return $cats->map(fn($c) => tap($c, fn($c) => $c->property_count = $counts[$c->id] ?? 0));
            });
            return response()->json(['error'=>false,'data'=>$cats]);
        } catch (Exception $e) {
            return response()->json(['error'=>true,'data'=>[]]);
        }
    }

    /* ========================================================================
       JSON: FAQS
    ======================================================================== */
    public function apiFaqs()
    {
        try {
            $faqs = Cache::remember('bw_faqs', 3600, fn() => Faq::where('status',1)->get());
            return response()->json(['error'=>false,'data'=>$faqs]);
        } catch (Exception $e) {
            return response()->json(['error'=>true,'data'=>[]]);
        }
    }

    /* ========================================================================
       JSON: TOGGLE FAVOURITE (protected)
       favourites table: user_id, property_id
    ======================================================================== */
    public function toggleFavourite(Request $request)
    {
        $u = session('bw_customer');
        if (!$u) return response()->json(['error'=>true,'message'=>'Not authenticated'],401);

        $v = Validator::make($request->all(), ['property_id'=>'required|integer']);
        if ($v->fails()) return response()->json(['error'=>true,'message'=>$v->errors()->first()]);

        $pid = (int)$request->property_id;
        $uid = (int)$u['id'];

        $exists = DB::table('favourites')->where('user_id',$uid)->where('property_id',$pid)->exists();
        if ($exists) {
            DB::table('favourites')->where('user_id',$uid)->where('property_id',$pid)->delete();
            return response()->json(['error'=>false,'message'=>'Removed from saved.','action'=>'removed']);
        }
        DB::table('favourites')->insert(['user_id'=>$uid,'property_id'=>$pid,'created_at'=>now(),'updated_at'=>now()]);
        return response()->json(['error'=>false,'message'=>'Saved to shortlist!','action'=>'added']);
    }

    /* ========================================================================
       JSON: INQUIRE (protected)
       interested_users table: customer_id, property_id, status (NO message column)
    ======================================================================== */
    public function inquire(Request $request)
    {
        $u = session('bw_customer');

        $v = Validator::make($request->all(), ['property_id'=>'required|integer']);
        if ($v->fails()) return response()->json(['error'=>true,'message'=>$v->errors()->first()]);

        try {
            $propId = (int)$request->property_id;

            if ($u) {
                // Logged-in buyer
                $custId = (int)$u['id'];
                $exists = DB::table('interested_users')
                    ->where('customer_id', $custId)->where('property_id', $propId)->exists();
                if (!$exists) {
                    DB::table('interested_users')->insert([
                        'customer_id' => $custId,
                        'property_id' => $propId,
                        'status'      => 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
                return response()->json(['error'=>false,'message'=>'Your enquiry has been sent! The owner will contact you soon.']);
            } else {
                // Guest buyer — auto-register or find existing
                $name   = trim($request->name   ?? '');
                $mobile = trim($request->mobile ?? '');
                if (!$name || !$mobile) {
                    return response()->json(['error'=>true,'message'=>'Please enter your name and mobile number.']);
                }

                // Find or create guest customer
                $guest = DB::table('customers')->where('mobile', $mobile)->first();
                if (!$guest) {
                    $custId = DB::table('customers')->insertGetId([
                        'name'              => $name,
                        'email'             => 'guest_'.$mobile.'@bigwein.com',
                        'mobile'            => $mobile,
                        'country_code'      => '+91',
                        'password'          => bcrypt(uniqid()),
                        'slug_id'           => 'guest-'.substr(uniqid(),0,8),
                        'logintype'         => '3',
                        'is_email_verified' => 1,
                        'isActive'          => 1,
                        'notification'      => 1,
                        'address'           => '',
                        'auth_id'           => uniqid('bw_'),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                } else {
                    $custId = $guest->id;
                }

                $exists = DB::table('interested_users')
                    ->where('customer_id', $custId)->where('property_id', $propId)->exists();
                if (!$exists) {
                    DB::table('interested_users')->insert([
                        'customer_id' => $custId,
                        'property_id' => $propId,
                        'status'      => 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
                return response()->json(['error'=>false,'message'=>'Enquiry sent! The owner will contact you on '.$mobile.' soon.']);
            }
        } catch (\Exception $e) {
            Log::error('Inquire: '.$e->getMessage());
            return response()->json(['error'=>true,'message'=>'Something went wrong. Please try again.'],500);
        }
    }

    /* ========================================================================
       JSON: MY PROFILE / FAVOURITES (protected)
    ======================================================================== */
    public function myProfile()
    {
        $u = session('bw_customer');
        if (!$u) return response()->json(['error'=>true,'message'=>'Not authenticated'],401);
        return response()->json(['error'=>false,'data'=>Customer::find($u['id'])]);
    }

    public function myFavourites()
    {
        $u = session('bw_customer');
        if (!$u) return response()->json(['error'=>true,'message'=>'Not authenticated'],401);
        $ids   = DB::table('favourites')->where('user_id',$u['id'])->pluck('property_id');
        $props = $this->propQuery()->whereIn('id',$ids)->get();
        return response()->json(['error'=>false,'data'=>$props]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BUYER DASHBOARD PAGES
    // ─────────────────────────────────────────────────────────────────────────

    private function requireBuyer()
    {
        $u = session('bw_customer');
        if (!$u) return redirect('/user/login?next='.urlencode(request()->path()));
        return null;
    }

    public function userDashboard()
    {
        if ($redir = $this->requireBuyer()) return $redir;
        $u = session('bw_customer');
        $uid = $u['id'];

        $savedIds    = DB::table('favourites')->where('user_id',$uid)->pluck('property_id');
        $savedProps  = $this->propQuery()->whereIn('propertys.id', $savedIds)->take(4)->get();
        $enquiries   = DB::table('interested_users as iu')
            ->join('propertys as p','p.id','=','iu.property_id')
            ->where('iu.user_id',$uid)
            ->select('iu.*','p.title','p.city','p.title_image','p.slug_id','p.price')
            ->orderByDesc('iu.created_at')->take(5)->get();
        $stats = [
            'saved'     => $savedIds->count(),
            'enquiries' => DB::table('interested_users')->where('user_id',$uid)->count(),
            'views'     => DB::table('property_views')->where('user_id',$uid)->count(),
        ];
        $s = self::settings(['currency_symbol','web_logo','company_name']);
        $customer = session('bw_customer');
        return view('frontend.user.dashboard', compact('savedProps','enquiries','stats','s','customer'));
    }

    public function userSaved()
    {
        if ($redir = $this->requireBuyer()) return $redir;
        $u = session('bw_customer');
        $savedIds   = DB::table('favourites')->where('user_id',$u['id'])->pluck('property_id');
        $properties = $this->propQuery()->whereIn('propertys.id', $savedIds)
            ->orderByDesc('propertys.created_at')->paginate(12);
        $s = self::settings(['currency_symbol','web_logo','company_name']);
        $customer = session('bw_customer');
        return view('frontend.user.saved', compact('properties','s','customer'));
    }

    public function userEnquiries()
    {
        if ($redir = $this->requireBuyer()) return $redir;
        $u = session('bw_customer');
        $enquiries = DB::table('interested_users as iu')
            ->leftJoin('propertys as p','p.id','=','iu.property_id')
            ->where('iu.user_id',$u['id'])
            ->select('iu.*','p.title as prop_title','p.city','p.title_image','p.slug_id','p.price','p.propery_type')
            ->orderByDesc('iu.created_at')->paginate(10);
        $s = self::settings(['currency_symbol','web_logo','company_name']);
        $customer = session('bw_customer');
        return view('frontend.user.enquiries', compact('enquiries','s','customer'));
    }

    public function userProfile()
    {
        if ($redir = $this->requireBuyer()) return $redir;
        $u        = Customer::find(session('bw_customer')['id']);
        $s        = self::settings(['currency_symbol','web_logo','company_name']);
        $customer = session('bw_customer');
        return view('frontend.user.profile', compact('u','s','customer'));
    }

    public function userProfileUpdate(Request $request)
    {
        if (!session('bw_customer')) return response()->json(['error'=>true,'message'=>'Not authenticated'],401);
        $uid = session('bw_customer')['id'];
        $data = ['name'=>$request->name,'mobile'=>$request->mobile,'updated_at'=>now()];
        if ($request->filled('password')) {
            if (strlen($request->password) < 6)
                return response()->json(['error'=>true,'message'=>'Password must be at least 6 characters.']);
            $data['password'] = \Hash::make($request->password);
        }
        Customer::where('id',$uid)->update($data);
        $updated = Customer::find($uid);
        session(['bw_customer' => $updated->toArray()]);
        return response()->json(['error'=>false,'message'=>'Profile updated successfully!']);
    }

    public function removeSaved(Request $request)
    {
        $u = session('bw_customer');
        if (!$u) return response()->json(['error'=>true,'message'=>'Not authenticated'],401);
        DB::table('favourites')->where('user_id',$u['id'])->where('property_id',$request->property_id)->delete();
        return response()->json(['error'=>false,'message'=>'Removed from saved properties.']);
    }
}
