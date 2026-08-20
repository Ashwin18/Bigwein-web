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

    private static function activeLangs()
    {
        try {
            return Cache::remember('bw_active_langs_full', 3600, function () {
                return DB::table('languages')->where('status', 1)->orderBy('id')->get();
            });
        } catch (\Exception $e) {
            return collect();
        }
    }

    // ── Base approved property query ──────────────────────────────────────────
    /** Check demo mode — query DB directly */
    private function isDemoOn(): bool
    {
        static $demoOn = null;
        if ($demoOn === null) {
            try {
                $val = DB::table('settings')
                    ->where('type', 'demo_mode_enabled')
                    ->value('data');
                // Default to true (show demo data) when setting not yet configured
                $demoOn = ($val === null) ? true : ($val === '1' || $val === 1);
            } catch (\Exception $e) {
                $demoOn = true;
            }
        }
        return $demoOn;
    }

    /**
     * Apply the global data mode to frontend queries.
     * Demo ON  => demo_seed records only.
     * Demo OFF => live/non-demo records only.
     */
    private function applyDemoFilter($q, string $table = 'propertys')
    {
        if ($this->isDemoOn()) {
            $q->where("{$table}.meta_keywords", 'demo_seed');
        } else {
            $q->where(function($x) use ($table) {
                $x->where("{$table}.meta_keywords", '!=', 'demo_seed')
                  ->orWhereNull("{$table}.meta_keywords");
            });
        }
        return $q;
    }

    private function propQuery()
    {
        $q = Property::with('customer:id,name,mobile,country_code')
            ->where('propertys.status', 1)
            ->where('propertys.request_status', 'approved')
            ->where(function ($x) {
                $x->whereNull('propertys.listing_type')
                  ->orWhere('propertys.listing_type', 'property');
            });

        return $this->applyDemoFilter($q, 'propertys');
    }

    /** Base approved project query with the same demo-mode behaviour. */
    private function projectQuery()
    {
        $q = Projects::query()
            ->where('projects.status', 1)
            ->where('projects.request_status', 'approved');

        return $this->applyDemoFilter($q, 'projects');
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
        $this->applyDemoFilter($countQ, 'propertys');
        $counts = $countQ->groupBy('category_id')->pluck('cnt', 'category_id');
        $categories->each(fn($c) => $c->property_count = $counts[$c->id] ?? 0);
        $categories = $categories->sortByDesc('property_count')->values();

        // Projects
        $projects = $this->projectQuery()->with(['category', 'translations'])
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
        try {
            $swCfg = \App\Http\Controllers\SearchSettingsController::load();
        } catch (\Exception $e) {
            $swCfg = [
                'enabled'          => false,
                'tabs'             => [
                    ['slug'=>'buy',        'label'=>'Buy',        'icon'=>'fa-house',  'active'=>true],
                    ['slug'=>'rent',       'label'=>'Rent',       'icon'=>'fa-key',    'active'=>true],
                    ['slug'=>'commercial', 'label'=>'Commercial', 'icon'=>'fa-store',  'active'=>true],
                    ['slug'=>'plots',      'label'=>'Plots',      'icon'=>'fa-map',    'active'=>true],
                    ['slug'=>'projects',   'label'=>'Projects',   'icon'=>'fa-city',   'active'=>true],
                ],
                'buy_subtypes'     => [
                    ['label'=>'Full House',  'slug'=>'fullhouse', 'active'=>true],
                    ['label'=>'Land / Plot', 'slug'=>'landplot',  'active'=>true],
                ],
                'rent_subtypes'    => [
                    ['label'=>'Full House',  'slug'=>'fullhouse', 'active'=>true],
                    ['label'=>'PG / Hostel', 'slug'=>'pghostel',  'active'=>true],
                    ['label'=>'Flatmates',   'slug'=>'flatmates', 'active'=>true],
                ],
                'bhk_options'      => ['1 BHK','2 BHK','3 BHK','4 BHK','5+ BHK'],
                'prop_statuses'    => ['Ready to Move','Under Construction','New Launch'],
                'commercial_types' => ['Office','Co-working Space','Shop / Showroom','Warehouse','Factory / Industrial'],
                'budget_buy'       => [
                    ['label'=>'Under ₹25L','min'=>'0',        'max'=>'2500000'],
                    ['label'=>'₹25L–₹50L', 'min'=>'2500000',  'max'=>'5000000'],
                    ['label'=>'₹50L–₹1Cr', 'min'=>'5000000',  'max'=>'10000000'],
                    ['label'=>'₹1Cr–₹2Cr', 'min'=>'10000000', 'max'=>'20000000'],
                    ['label'=>'Above ₹2Cr','min'=>'20000000', 'max'=>''],
                ],
                'budget_rent'      => [
                    ['label'=>'Under ₹10k','min'=>'0',     'max'=>'10000'],
                    ['label'=>'₹10k–₹25k', 'min'=>'10000', 'max'=>'25000'],
                    ['label'=>'₹25k–₹50k', 'min'=>'25000', 'max'=>'50000'],
                    ['label'=>'Above ₹50k','min'=>'50000', 'max'=>''],
                ],
            ];
        }

        $activeLangs  = self::activeLangs();
        try {
            $siteCfg      = \App\Http\Controllers\SiteSettingsController::load();
        } catch (\Exception $e) { $siteCfg = []; }
        $announcement = $siteCfg['announcement'] ?? [];
        return view('frontend.home', compact(
            'featured','categories','projects','sliders','faqs','s','cities','customer','searchCats','commercialTypes','swCfg','activeLangs','siteCfg','announcement'
        ));
    }

    /* ========================================================================
       PAGE: PROPERTIES LISTING
    ======================================================================== */
    public function properties(Request $request)
    {
        $q = $this->propQuery();

        // Property marketplace only. Business-for-sale records have their own screen.
        $q->where(function ($x) {
            $x->whereNull('propertys.listing_type')
              ->orWhere('propertys.listing_type', 'property');
        });

        // Support legacy ?type= and homepage ?propery_type=.
        $ptype = $request->filled('propery_type') ? $request->propery_type : $request->type;
        if ($ptype !== null && $ptype !== '') {
            $q->where('propertys.propery_type', (int) $ptype);
        }

        // Free-text search: public search uses title + city + state only (not street address).
        if ($request->filled('search')) {
            $sr = trim((string) $request->search);
            $q->where(function ($x) use ($sr) {
                $x->where('propertys.title', 'LIKE', "%{$sr}%")
                  ->orWhere('propertys.city', 'LIKE', "%{$sr}%")
                  ->orWhere('propertys.state', 'LIKE', "%{$sr}%");
            });
        }

        if ($request->filled('category_id')) {
            $q->where('propertys.category_id', $request->category_id);
        }

        // Location intentionally searches City + State only. Full address stays private.
        if ($request->filled('city')) {
            $location = trim((string) $request->city);
            $q->where(function ($qc) use ($location) {
                $qc->where('propertys.city', 'LIKE', "%{$location}%")
                   ->orWhere('propertys.state', 'LIKE', "%{$location}%");
            });
        }

        if ($request->filled('min_price')) $q->where('propertys.price', '>=', (float) $request->min_price);
        if ($request->filled('max_price')) $q->where('propertys.price', '<=', (float) $request->max_price);

        // Search Settings subtype. Structured DB fields are primary; legacy/demo rows get
        // category/title fallback because older seeded rows have NULL sub_type/commercial_type.
        $subSlug  = strtolower(trim((string) $request->input('sub_type', '')));
        $subLabel = trim((string) $request->input('subtype_label', ''));
        $purpose  = strtolower(trim((string) $request->input('listing_purpose', '')));
        $norm = fn($v) => preg_replace('/[^a-z0-9]+/', '', strtolower((string) $v));
        $key = $norm($subSlug ?: $subLabel);

        $commercialKeys = ['commercial','office','officespace','coworking','coworkingspace','shop','showroom','shopshowroom','warehouse','industrial','factory','factoryindustrial'];
        $plotKeys       = ['plot','land','landplot','residentialplot','agricultural','agriculturalland','farmland'];
        $pgKeys         = ['pg','pghostel','hostel','pghostel','boyspg','girlspg'];
        $nonBhkKeys     = array_merge($commercialKeys, $plotKeys, $pgKeys, ['flatmates']);

        if ($key !== '') {
            $categoryIdsFor = function (array $needles) {
                return DB::table('categories')
                    ->where(function ($cq) use ($needles) {
                        foreach ($needles as $n) {
                            $cq->orWhere('category', 'LIKE', "%{$n}%")
                               ->orWhere('slug_id', 'LIKE', "%{$n}%");
                        }
                    })->pluck('id');
            };

            if (in_array($key, $commercialKeys, true) || $purpose === 'lease') {
                $commercialIds = $categoryIdsFor(['Commercial']);
                $wanted = $subLabel ?: $subSlug;
                $tokens = array_values(array_filter(preg_split('/[\s\/-]+/', strtolower($wanted)), fn($w) => strlen($w) >= 3));
                $q->where(function ($sq) use ($commercialIds, $wanted, $tokens) {
                    if ($commercialIds->isNotEmpty()) $sq->whereIn('propertys.category_id', $commercialIds);
                    $sq->where(function ($tq) use ($wanted, $tokens) {
                        $tq->where('propertys.commercial_type', 'LIKE', "%{$wanted}%")
                           ->orWhere('propertys.sub_type', 'LIKE', "%{$wanted}%")
                           ->orWhere('propertys.title', 'LIKE', "%{$wanted}%");
                        foreach ($tokens as $token) {
                            $tq->orWhere('propertys.commercial_type', 'LIKE', "%{$token}%")
                               ->orWhere('propertys.sub_type', 'LIKE', "%{$token}%")
                               ->orWhere('propertys.title', 'LIKE', "%{$token}%")
                               ->orWhere('propertys.description', 'LIKE', "%{$token}%");
                        }
                    });
                });
            } elseif (in_array($key, $plotKeys, true)) {
                $plotIds = $categoryIdsFor(['Plot','Land']);
                if ($plotIds->isNotEmpty()) $q->whereIn('propertys.category_id', $plotIds);
                if (str_contains($key, 'agricultural') || str_contains($key, 'farm')) {
                    $q->where(function ($sq) {
                        $sq->where('propertys.sub_type', 'LIKE', '%agric%')
                           ->orWhere('propertys.title', 'LIKE', '%agric%')
                           ->orWhere('propertys.title', 'LIKE', '%farm%')
                           ->orWhere('propertys.description', 'LIKE', '%agric%')
                           ->orWhere('propertys.description', 'LIKE', '%farm%');
                    });
                }
            } elseif (in_array($key, $pgKeys, true)) {
                $pgIds = $categoryIdsFor(['PG','Hostel']);
                if ($pgIds->isNotEmpty()) $q->whereIn('propertys.category_id', $pgIds);
            } elseif ($key === 'villa') {
                $ids = $categoryIdsFor(['Villa']);
                $q->where(function ($sq) use ($ids, $subLabel) {
                    if ($ids->isNotEmpty()) $sq->whereIn('propertys.category_id', $ids);
                    if ($subLabel) $sq->orWhere('propertys.sub_type', 'LIKE', "%{$subLabel}%");
                });
            } elseif ($key === 'townhouse') {
                $ids = $categoryIdsFor(['Townhouse']);
                if ($ids->isNotEmpty()) $q->whereIn('propertys.category_id', $ids);
            } elseif ($key === 'flatmates') {
                $q->where('propertys.rentduration', 'Monthly')
                  ->where(function ($sq) {
                      $sq->where('propertys.sub_type', 'LIKE', '%flatmate%')
                         ->orWhere('propertys.title', 'LIKE', '%flatmate%')
                         ->orWhere('propertys.description', 'LIKE', '%flatmate%');
                  });
            } elseif (!in_array($key, ['residential','fullhouse','apartment'], true)) {
                // Generic custom subtype configured by admin.
                $wanted = $subLabel ?: $subSlug;
                $q->where(function ($sq) use ($wanted) {
                    $sq->where('propertys.sub_type', 'LIKE', "%{$wanted}%")
                       ->orWhere('propertys.commercial_type', 'LIKE', "%{$wanted}%")
                       ->orWhere('propertys.title', 'LIKE', "%{$wanted}%");
                });
            }
            // Residential / Full House / Apartment are intentionally broad when legacy rows
            // don't have sub_type populated; BHK/category/status still narrow the result.
        }

        // BHK: structured Bedroom parameter first, with title fallback for older demo records.
        $isNonBhkSearch = in_array($key, $nonBhkKeys, true) || $purpose === 'lease';
        if ($request->filled('bhk') && !$isNonBhkSearch) {
            $bhkNums = array_values(array_unique(array_filter(array_map(
                fn($b) => (int) filter_var(trim($b), FILTER_SANITIZE_NUMBER_INT),
                explode(',', (string) $request->bhk)
            ))));

            if ($bhkNums) {
                $propIds = DB::table('assign_parameters as ap')
                    ->join('parameters as p', 'p.id', '=', 'ap.parameter_id')
                    ->whereRaw('LOWER(p.name) LIKE ?', ['%bedroom%'])
                    ->whereIn(DB::raw('CAST(ap.value AS UNSIGNED)'), $bhkNums)
                    ->pluck('ap.property_id');

                $q->where(function ($bq) use ($propIds, $bhkNums) {
                    if ($propIds->isNotEmpty()) $bq->whereIn('propertys.id', $propIds);
                    foreach ($bhkNums as $n) {
                        $bq->orWhere('propertys.title', 'LIKE', "%{$n}BHK%")
                           ->orWhere('propertys.title', 'LIKE', "%{$n} BHK%");
                    }
                });
            }
        }

        if ($request->filled('prop_status')) {
            $q->where('propertys.prop_status', $request->prop_status);
        }

        // Dedicated commercial multi-select used by the Commercial search panel.
        if ($request->filled('comm_types')) {
            $types = array_values(array_filter(array_map('trim', explode(',', (string) $request->comm_types))));
            $commercialIds = DB::table('categories')->where('category', 'LIKE', '%Commercial%')->pluck('id');
            if ($commercialIds->isNotEmpty()) $q->whereIn('propertys.category_id', $commercialIds);
            $q->where(function ($qc) use ($types) {
                foreach ($types as $t) {
                    $qc->orWhere('propertys.commercial_type', 'LIKE', "%{$t}%")
                       ->orWhere('propertys.sub_type', 'LIKE', "%{$t}%")
                       ->orWhere('propertys.title', 'LIKE', "%{$t}%")
                       ->orWhere('propertys.description', 'LIKE', "%{$t}%");
                }
            });
        }

        match ($request->input('sort','')) {
            'price_asc'  => $q->orderBy('propertys.price'),
            'price_desc' => $q->orderByDesc('propertys.price'),
            'newest'     => $q->orderByDesc('propertys.created_at'),
            default      => $q->orderByDesc('propertys.is_premium')->orderByDesc('propertys.total_click'),
        };

        $properties = $q->paginate(9)->withQueryString();
        $categories = Category::where('status',1)->with('translations')->get();
        $citiesQ = DB::table('propertys')->where('status',1)->where('request_status','approved')
            ->where(function ($x) { $x->whereNull('listing_type')->orWhere('listing_type','property'); })
            ->whereNotNull('city')->where('city','!=','');
        $this->applyDemoFilter($citiesQ, 'propertys');
        $cities = $citiesQ->distinct()->orderBy('city')->pluck('city');
        $s        = self::settings(['currency_symbol','web_logo','company_name']);
        $customer = session('bw_customer');

        $filters = $request->only([
            'type','propery_type','search','city','min_price','max_price',
            'category_id','sort','bhk','prop_status','sub_type','subtype_label','listing_purpose','comm_types'
        ]);
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        return view('frontend.properties.index', compact(
            'properties','categories','cities','s','filters','customer'
        ));
    }

    /* ========================================================================
       PAGE: BUSINESS FOR SALE
    ======================================================================== */
    public function businesses(Request $request)
    {
        $q = Property::query()
            ->where('propertys.status', 1)
            ->where('propertys.request_status', 'approved')
            ->where('propertys.listing_type', 'business');
        $this->applyDemoFilter($q, 'propertys');

        if ($request->filled('city')) {
            $location = trim((string) $request->city);
            $q->where(function ($x) use ($location) {
                $x->where('propertys.city', 'LIKE', "%{$location}%")
                  ->orWhere('propertys.state', 'LIKE', "%{$location}%");
            });
        }

        if ($request->filled('btype')) {
            $type = trim((string) $request->btype);
            $tokens = array_values(array_filter(preg_split('/[\s\/-]+/', strtolower($type)), fn($w) => strlen($w) >= 3));
            $q->where(function ($x) use ($type, $tokens) {
                $x->where('propertys.business_type', 'LIKE', "%{$type}%")
                  ->orWhere('propertys.title', 'LIKE', "%{$type}%");
                foreach ($tokens as $token) {
                    $x->orWhere('propertys.business_type', 'LIKE', "%{$token}%")
                      ->orWhere('propertys.title', 'LIKE', "%{$token}%")
                      ->orWhere('propertys.description', 'LIKE', "%{$token}%");
                }
            });
        }

        if ($request->filled('price_min')) $q->where('propertys.price', '>=', (float) $request->price_min);
        if ($request->filled('price_max')) $q->where('propertys.price', '<=', (float) $request->price_max);

        $businesses = $q->orderByDesc('propertys.is_premium')
            ->orderByDesc('propertys.created_at')
            ->paginate(10)->withQueryString();

        // Dropdown values come from actual records in the currently selected Demo/Live mode,
        // then Search Settings labels can still submit values even when a type has zero results.
        $btQ = DB::table('propertys')->where('status',1)->where('request_status','approved')
            ->where('listing_type','business')->whereNotNull('business_type')->where('business_type','!=','');
        $this->applyDemoFilter($btQ, 'propertys');
        $btypes = $btQ->distinct()->orderBy('business_type')->pluck('business_type');

        try {
            $cfgTypes = collect(\App\Http\Controllers\SearchSettingsController::load()['tab_subtypes']['business'] ?? [])
                ->where('active', true)->pluck('label')->filter();
            $btypes = $btypes->merge($cfgTypes)->unique()->values();
        } catch (\Throwable $e) {}

        $s        = self::settings(['currency_symbol','web_logo','company_name']);
        $customer = session('bw_customer');
        return view('frontend.businesses.index', compact('businesses','btypes','s','customer'));
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
        // Keep the in-memory model in sync so the page shows the count including this view.
        $prop->total_click = ((int) ($prop->total_click ?? 0)) + 1;

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

        $siteCfg = \App\Http\Controllers\SiteSettingsController::load();
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
        $q = $this->projectQuery()->with(['category','translations']);

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
        $baseQ    = $this->projectQuery();
        $stats = [
            'total'        => (clone $baseQ)->count(),
            'ready'        => (clone $baseQ)->where('type','Ready to Move')->count(),
            'construction' => (clone $baseQ)->where('type','Under Construction')->count(),
            'new_launch'   => (clone $baseQ)->where('type','New Launch')->count(),
            'cities'       => (clone $baseQ)->whereNotNull('city')->where('city','!=','')->distinct('city')->count('city'),
        ];

        // Filter options
        $cities     = $this->projectQuery()
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
        $project = $this->projectQuery()->with(['category','translations','gallary_images','plans','project_documetns'])
            ->where('slug_id', $slug)
            ->firstOrFail();

        // Increment views
        \DB::table('projects')->where('id',$project->id)->increment('total_click');
        // Keep the in-memory model in sync so the page shows the count including this view.
        $project->total_click = ((int) ($project->total_click ?? 0)) + 1;

        // Similar projects (same category, exclude current)
        $similar = $this->projectQuery()->where('category_id',$project->category_id)
            ->where('id','!=',$project->id)
            ->inRandomOrder()->take(3)->get();

        if ($similar->count() < 3) {
            $more = $this->projectQuery()->where('id','!=',$project->id)
                ->whereNotIn('id',$similar->pluck('id'))
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
    
    public function storeBusiness(\Illuminate\Http\Request $request)
    {
        if (!session("bw_customer")) {
            return response()->json(["success"=>false,"message"=>"Please login first"]);
        }
        $customer = session("bw_customer");

        $slug = \Illuminate\Support\Str::slug($request->title)."-".substr(uniqid(),0,6);

        $meta = json_encode([
            "turnover"         => $request->turnover,
            "profit_margin"    => $request->profit_margin,
            "employees"        => $request->employees,
            "established_year" => $request->established_year,
            "reason_selling"   => $request->reason_selling,
            "lease_type"       => $request->lease_type,
            "includes"         => $request->includes,
        ]);

        $imgName = null;
        if ($request->hasFile("title_image")) {
            $file    = $request->file("title_image");
            $imgName = time()."_".$file->getClientOriginalName();
            $file->move(public_path("images/property_title_img"), $imgName);
        }

        // Generate reference number
        do {
            $ref = "BW".date("Y").strtoupper(substr(bin2hex(random_bytes(3)),0,6));
        } while (\DB::table("propertys")->where("reference_no",$ref)->exists());

        $id = \DB::table("propertys")->insertGetId([
            "title"          => $request->title,
            "slug_id"        => $slug,
            "description"    => $request->description,
            "city"           => $request->city,
            "state"          => $request->state ?? "",
            "address"        => $request->address ?? "",
            "client_address" => $request->city,
            "price"          => $request->price ?? 0,
            "listing_type"   => "business",
            "business_type"  => $request->business_type,
            "business_meta"  => $meta,
            "title_image"    => $imgName,
            "status"         => 1,
            "request_status" => "pending",
            "added_by"       => $customer["id"],
            "propery_type"   => 2,
            "reference_no"   => $ref,
            "created_at"     => now(),
            "updated_at"     => now(),
        ]);

        return response()->json(["success"=>true,"id"=>$id,"message"=>"Business listed successfully!"]);
    }

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
            $q = $this->projectQuery()->with(['category','translations']);
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
            $cacheKey = 'bw_categories_' . ($this->isDemoOn() ? 'demo' : 'live');
            $cats = Cache::remember($cacheKey, 3600, function () {
                $cats   = Category::where('status',1)->with('translations')->get();
                $countsQ = DB::table('propertys')
                    ->select('category_id', DB::raw('COUNT(*) as cnt'))
                    ->where(['status'=>1,'request_status'=>'approved']);
                $this->applyDemoFilter($countsQ, 'propertys');
                $counts = $countsQ->groupBy('category_id')->pluck('cnt','category_id');
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

        $savedIds   = DB::table('favourites')->where('user_id', $uid)->pluck('property_id');
        $savedProps = collect();
        if ($savedIds->isNotEmpty()) {
            $savedProps = $this->propQuery()->whereIn('propertys.id', $savedIds)->take(4)->get();
        }
        $enquiries = DB::table('interested_users as iu')
            ->leftJoin('propertys as p', 'p.id', '=', 'iu.property_id')
            ->where('iu.customer_id', $uid)
            ->select('iu.*', 'p.title', 'p.city', 'p.title_image', 'p.slug_id', 'p.price')
            ->orderByDesc('iu.created_at')->take(5)->get();
        $viewCount = 0;
        try { $viewCount = DB::table('property_views')->where('user_id', $uid)->count(); } catch(\Exception $e) {}
        $stats = [
            'saved'     => $savedIds->count(),
            'enquiries' => DB::table('interested_users')->where('customer_id', $uid)->count(),
            'views'     => $viewCount,
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
        $properties = $savedIds->isEmpty()
            ? (new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 12))
            : $this->propQuery()->whereIn('propertys.id', $savedIds)
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
            ->where('iu.customer_id',$u['id'])
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

    /* ================================================================
       LANGUAGE SWITCH
    ================================================================ */
    public function switchLang(Request $request, $code)
    {
        // Validate code against active languages
        $allowed = Cache::remember('bw_active_langs', 3600, function() {
            try {
                return \DB::table('languages')->where('status', 1)->pluck('code')->toArray();
            } catch (\Exception $e) {
                return ['en'];
            }
        });

        $code = in_array($code, $allowed) ? $code : 'en';

        session(['locale' => $code, 'bw_lang' => $code]);
        app()->setLocale($code);

        return redirect()->back()->withCookie(cookie('bw_locale', $code, 60 * 24 * 30)); // 30 day cookie
    }
}
