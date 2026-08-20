<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteSettingsController extends Controller
{
    /* ── Default values (current behaviour = no change if setting missing) ── */
    public static function defaults(): array
    {
        return [
            // 1. Homepage sections
            'sections' => [
                ['key'=>'categories',  'label'=>'Browse by Category',   'title'=>'Browse by Category',   'subtitle'=>'Explore properties that match your needs',      'show'=>true],
                ['key'=>'featured',    'label'=>'Featured Properties',   'title'=>'Featured Properties',   'subtitle'=>'Properties handpicked for you',                 'show'=>true],
                ['key'=>'projects',    'label'=>'Featured Projects',     'title'=>'Featured Projects',     'subtitle'=>'Top residential & commercial projects',          'show'=>true],
                ['key'=>'why',         'label'=>'Why Choose BigWein?',   'title'=>'Why Choose BigWein?',   'subtitle'=>'The smarter way to buy, sell and rent property', 'show'=>true],
                ['key'=>'faq',         'label'=>'FAQ Section',           'title'=>'Frequently Asked Questions','subtitle'=>'Answers for buyers, sellers and owners',    'show'=>true],
                ['key'=>'app_cta',     'label'=>'Download App Banner',   'title'=>'Download App Banner',   'subtitle'=>'',                                              'show'=>true],
            ],
            // 2. Listing filters
            'listing_filters' => [
                ['key'=>'price',       'label'=>'Price Range',         'show'=>true],
                ['key'=>'bhk',         'label'=>'BHK Count',           'show'=>true],
                ['key'=>'status',      'label'=>'Property Status',     'show'=>true],
                ['key'=>'furnishing',  'label'=>'Furnishing',          'show'=>true],
                ['key'=>'area',        'label'=>'Area (sqft)',         'show'=>true],
                ['key'=>'facing',      'label'=>'Facing Direction',    'show'=>true],
                ['key'=>'age',         'label'=>'Age of Building',     'show'=>false],
                ['key'=>'floor',       'label'=>'Floor Number',        'show'=>false],
            ],
            'results_per_page'  => 12,
            'default_sort'      => 'latest',
            // 3. Property card fields
            'card_fields' => [
                ['key'=>'price',     'label'=>'Price',           'show'=>true],
                ['key'=>'location',  'label'=>'Location/City',   'show'=>true],
                ['key'=>'bhk',       'label'=>'BHK Badge',       'show'=>true],
                ['key'=>'area',      'label'=>'Area (sqft)',     'show'=>true],
                ['key'=>'category',  'label'=>'Category Badge',  'show'=>true],
                ['key'=>'premium',   'label'=>'PREMIUM Badge',   'show'=>true],
                ['key'=>'call_btn',  'label'=>'Call Button',     'show'=>true],
                ['key'=>'wa_btn',    'label'=>'WhatsApp Button', 'show'=>true],
                ['key'=>'heart',     'label'=>'Save/Heart',      'show'=>true],
                ['key'=>'views',     'label'=>'Views Count',     'show'=>false],
            ],
            // 4. Notification templates
            'notif_templates' => [
                'approved_subject' => 'Your property "{title}" is now live on BigWein!',
                'approved_body'    => "Dear {owner_name},\n\nGreat news! Your property \"{title}\" has been approved and is now live on BigWein.\n\nBuyers can now find and enquire about your property.\n\nRegards,\nBigWein Team",
                'rejected_subject' => 'Update on your property "{title}" submission',
                'rejected_body'    => "Dear {owner_name},\n\nUnfortunately, your property \"{title}\" was not approved at this time.\n\nPlease review our listing guidelines and resubmit.\n\nRegards,\nBigWein Team",
                'enquiry_subject'  => 'New enquiry for your property "{title}"',
                'enquiry_body'     => "Dear {owner_name},\n\nYou have received a new enquiry from {buyer_name} ({buyer_mobile}) for your property \"{title}\".\n\nMessage: {message}\n\nRegards,\nBigWein Team",
                'welcome_subject'  => 'Welcome to BigWein!',
                'welcome_body'     => "Dear {name},\n\nWelcome to BigWein — India's zero brokerage property platform!\n\nStart exploring verified properties today.\n\nRegards,\nBigWein Team",
            ],
            // 5. SEO templates
            'seo' => [
                'property_title'  => '{title} in {city} | BigWein',
                'property_desc'   => '{bhk} {category} for {type} in {city}, {state}. Price: {price}. Contact owner directly — Zero brokerage.',
                'project_title'   => '{name} by {builder} in {city} | BigWein Projects',
                'project_desc'    => '{name} — {type} project in {city}. {status}. Browse floor plans and pricing.',
                'home_title'      => 'BigWein — Buy, Sell & Rent Property | Zero Brokerage',
                'home_desc'       => 'Find verified properties for sale and rent across India. Connect directly with owners. Zero brokerage guaranteed.',
                'listing_title'   => 'Properties in {city} | Buy & Rent | BigWein',
                'listing_desc'    => 'Browse {count}+ verified properties in {city}. Buy, rent or invest — no brokerage, direct owner contact.',
            ],
            // 6. Announcement banner
            'announcement' => [
                'show'       => false,
                'text'       => '🎉 Zero brokerage for all listings this month!',
                'color'      => '#E5343A',
                'text_color' => '#ffffff',
                'link'       => '',
                'dismissible'=> true,
            ],
        ];
    }

    /* ── Load from DB with fallback to defaults ── */
    public static function load(string $key = null)
    {
        $cacheKey = 'bw_site_settings';
        $all = Cache::remember($cacheKey, 1800, function() {
            $defaults = self::defaults();
            $keys = ['sections','listing_filters','results_per_page','default_sort',
                     'card_fields','notif_templates','seo','announcement'];
            foreach ($keys as $k) {
                $raw = Setting::where('type', 'site_'.$k)->value('data');
                if ($raw) {
                    $defaults[$k] = json_decode($raw, true) ?? $defaults[$k];
                }
            }
            return $defaults;
        });
        return $key ? ($all[$key] ?? self::defaults()[$key] ?? null) : $all;
    }

    public static function clearCache()
    {
        Cache::forget('bw_site_settings');
    }

    /* ── Admin index ── */
    public function index()
    {
        $cfg = self::load();
        return view('site-settings.index', compact('cfg'));
    }

    /* ── Save any key ── */
    public function save(Request $request)
    {
        $key   = $request->key;
        $value = $request->value;

        $allowed = ['sections','listing_filters','results_per_page','default_sort',
                    'card_fields','notif_templates','seo','announcement'];
        if (!in_array($key, $allowed)) {
            return response()->json(['error'=>true,'message'=>'Invalid key']);
        }

        Setting::updateOrCreate(
            ['type' => 'site_'.$key],
            ['data' => is_array($value) ? json_encode($value) : $value]
        );
        self::clearCache();
        return response()->json(['success'=>true, 'message'=>'Saved successfully!']);
    }
}
