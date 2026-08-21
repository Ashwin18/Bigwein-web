<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchSettingsController extends Controller
{
    // Phase 1 compatibility: BHK/property-status settings remain active here until
    // the posting and search consumers migrate to Property Attribute Masters in Phase 3.
    /* ─── DEFAULTS ────────────────────────────────────────────────────────── */
    public static function defaults(): array
    {
        return [
            'enabled' => true,

            // Tabs — order here = order on homepage
            'tabs' => [
                ['slug'=>'buy',      'label'=>'Buy',              'icon'=>'fa-house',         'active'=>true],
                ['slug'=>'rent',     'label'=>'Rent',             'icon'=>'fa-key',           'active'=>true],
                ['slug'=>'lease',    'label'=>'Lease',            'icon'=>'fa-file-contract', 'active'=>true],
                ['slug'=>'business', 'label'=>'Business For Sale','icon'=>'fa-store',         'active'=>true],
            ],

            // Subtypes per tab — keyed by tab slug
            'tab_subtypes' => [
                'buy' => [
                    ['label'=>'Residential',  'slug'=>'residential', 'active'=>true],
                    ['label'=>'Commercial',   'slug'=>'commercial',  'active'=>true],
                    ['label'=>'Land / Plot',  'slug'=>'landplot',    'active'=>true],
                    ['label'=>'Villa',        'slug'=>'villa',       'active'=>true],
                    ['label'=>'Apartment',    'slug'=>'apartment',   'active'=>true],
                ],
                'rent' => [
                    ['label'=>'Full House',  'slug'=>'fullhouse', 'active'=>true],
                    ['label'=>'PG / Hostel', 'slug'=>'pghostel',  'active'=>true],
                    ['label'=>'Flatmates',   'slug'=>'flatmates', 'active'=>true],
                    ['label'=>'Apartment',   'slug'=>'apartment', 'active'=>true],
                    ['label'=>'Villa',       'slug'=>'villa',     'active'=>true],
                ],
                'lease' => [
                    ['label'=>'Office Space',   'slug'=>'office',      'active'=>true],
                    ['label'=>'Shop/Showroom',  'slug'=>'shop',        'active'=>true],
                    ['label'=>'Warehouse',      'slug'=>'warehouse',   'active'=>true],
                    ['label'=>'Industrial',     'slug'=>'industrial',  'active'=>true],
                    ['label'=>'Co-working',     'slug'=>'coworking',   'active'=>true],
                ],
                'business' => [
                    ['label'=>'Restaurant',        'slug'=>'restaurant', 'active'=>true],
                    ['label'=>'Retail Store',      'slug'=>'retail',     'active'=>true],
                    ['label'=>'Franchise',         'slug'=>'franchise',  'active'=>true],
                    ['label'=>'Hotel/Hospitality', 'slug'=>'hotel',      'active'=>true],
                    ['label'=>'Manufacturing',     'slug'=>'mfg',        'active'=>true],
                ],
            ],

            // BHK options (shared across tabs)
            'bhk_options' => ['1 BHK','2 BHK','3 BHK','4 BHK','5+ BHK'],

            // Property statuses
            'prop_statuses' => ['Ready to Move','Under Construction','New Launch'],

            // Budget chips
            'budget_buy' => [
                ['label'=>'Under ₹25L', 'min'=>'0',        'max'=>'2500000'],
                ['label'=>'₹25L–₹50L',  'min'=>'2500000',  'max'=>'5000000'],
                ['label'=>'₹50L–₹1Cr',  'min'=>'5000000',  'max'=>'10000000'],
                ['label'=>'₹1Cr–₹2Cr',  'min'=>'10000000', 'max'=>'20000000'],
                ['label'=>'Above ₹2Cr', 'min'=>'20000000', 'max'=>''],
            ],
            'budget_rent' => [
                ['label'=>'Under ₹10k', 'min'=>'0',     'max'=>'10000'],
                ['label'=>'₹10k–₹25k',  'min'=>'10000', 'max'=>'25000'],
                ['label'=>'₹25k–₹50k',  'min'=>'25000', 'max'=>'50000'],
                ['label'=>'Above ₹50k', 'min'=>'50000', 'max'=>''],
            ],
        ];
    }

    /* ─── LOAD FROM DB ────────────────────────────────────────────────────── */
    public static function load(): array
    {
        return Cache::remember('bw_search_cfg', 1800, function () {
            $cfg = self::defaults();
            $keys = ['tabs','tab_subtypes','bhk_options','prop_statuses','budget_buy','budget_rent'];
            foreach ($keys as $k) {
                try {
                    $raw = Setting::where('type', 'sw_'.$k)->value('data');
                    if ($raw) {
                        $decoded = json_decode($raw, true);
                        if ($decoded !== null) $cfg[$k] = $decoded;
                    }
                } catch (\Exception $e) {}
            }
            return $cfg;
        });
    }

    public static function clearCache(): void { Cache::forget('bw_search_cfg'); }

    /* ─── ADMIN INDEX ─────────────────────────────────────────────────────── */
    public function index()
    {
        $cfg = self::load();
        return view('search-settings.index', compact('cfg'));
    }

    /* ─── SAVE ────────────────────────────────────────────────────────────── */
    public function save(Request $request)
    {
        $key   = $request->key;
        $value = $request->value;
        $allowed = ['tabs','tab_subtypes','bhk_options','prop_statuses','budget_buy','budget_rent'];
        if (!in_array($key, $allowed)) {
            return response()->json(['error'=>true,'message'=>'Invalid key']);
        }
        Setting::updateOrCreate(
            ['type' => 'sw_'.$key],
            ['data' => is_array($value) ? json_encode($value) : json_encode($value)]
        );
        self::clearCache();
        return response()->json(['success'=>true,'message'=>'Saved!']);
    }
}
