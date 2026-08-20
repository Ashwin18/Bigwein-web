<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteSettingsController extends Controller
{
    public static function defaults(): array
    {
        return [
            'sections' => [
                ['key'=>'categories', 'label'=>'Browse by Category',  'title'=>'Browse by Category',  'subtitle'=>'Explore properties that match your needs', 'show'=>true],
                ['key'=>'featured',   'label'=>'Featured Properties', 'title'=>'Featured Properties', 'subtitle'=>'Properties handpicked for you',            'show'=>true],
                ['key'=>'projects',   'label'=>'Featured Projects',   'title'=>'Featured Projects',   'subtitle'=>'Top residential & commercial projects',     'show'=>true],
                ['key'=>'why',        'label'=>'Why Choose BigWein?', 'title'=>'Why Choose BigWein?', 'subtitle'=>'The smarter way to find property',          'show'=>true],
                ['key'=>'faq',        'label'=>'FAQ Section',         'title'=>'Frequently Asked Questions','subtitle'=>'Answers for buyers and owners',       'show'=>true],
                ['key'=>'app_cta',    'label'=>'Download App Banner', 'title'=>'Download App Banner', 'subtitle'=>'',                                          'show'=>true],
            ],
            'listing_filters' => [
                ['key'=>'price',      'label'=>'Price Range',      'show'=>true],
                ['key'=>'bhk',        'label'=>'BHK Count',        'show'=>true],
                ['key'=>'status',     'label'=>'Property Status',  'show'=>true],
                ['key'=>'furnishing', 'label'=>'Furnishing',       'show'=>true],
                ['key'=>'area',       'label'=>'Area (sqft)',      'show'=>true],
                ['key'=>'facing',     'label'=>'Facing Direction', 'show'=>true],
                ['key'=>'age',        'label'=>'Age of Building',  'show'=>false],
                ['key'=>'floor',      'label'=>'Floor Number',     'show'=>false],
            ],
            'results_per_page' => 12,
            'default_sort'     => 'latest',
            'card_fields' => [
                ['key'=>'price',    'label'=>'Price',           'show'=>true],
                ['key'=>'location', 'label'=>'Location/City',  'show'=>true],
                ['key'=>'bhk',      'label'=>'BHK Badge',      'show'=>true],
                ['key'=>'area',     'label'=>'Area (sqft)',    'show'=>true],
                ['key'=>'category', 'label'=>'Category Badge', 'show'=>true],
                ['key'=>'premium',  'label'=>'PREMIUM Badge',  'show'=>true],
                ['key'=>'call_btn', 'label'=>'Call Button',    'show'=>true],
                ['key'=>'wa_btn',   'label'=>'WhatsApp Button','show'=>true],
                ['key'=>'heart',    'label'=>'Save/Heart',     'show'=>true],
                ['key'=>'views',    'label'=>'Views Count',    'show'=>false],
            ],
            'notif_templates' => [
                'approved_subject' => 'Your property "{title}" is now live on BigWein!',
                'approved_body'    => "Dear {owner_name},\n\nYour property \"{title}\" has been approved and is now live.\n\nRegards,\nBigWein Team",
                'rejected_subject' => 'Update on your property "{title}" submission',
                'rejected_body'    => "Dear {owner_name},\n\nYour property \"{title}\" was not approved at this time.\n\nRegards,\nBigWein Team",
                'enquiry_subject'  => 'New enquiry for your property "{title}"',
                'enquiry_body'     => "Dear {owner_name},\n\nNew enquiry from {buyer_name} ({buyer_mobile}) for \"{title}\".\n\nMessage: {message}\n\nRegards,\nBigWein Team",
                'welcome_subject'  => 'Welcome to BigWein!',
                'welcome_body'     => "Dear {name},\n\nWelcome to BigWein!\n\nRegards,\nBigWein Team",
            ],
            'seo' => [
                'property_title' => '{title} in {city} | BigWein',
                'property_desc'  => '{bhk} {category} for {type} in {city}. Price: {price}. Zero brokerage.',
                'project_title'  => '{name} by {builder} in {city} | BigWein',
                'project_desc'   => '{name} in {city}. {status}.',
                'home_title'     => 'BigWein — Buy, Sell & Rent Property | Zero Brokerage',
                'home_desc'      => 'Find verified properties for sale and rent across India. Zero brokerage.',
                'listing_title'  => 'Properties in {city} | BigWein',
                'listing_desc'   => 'Browse verified properties in {city}.',
            ],
            'announcement' => [
                'show'        => false,
                'text'        => '',
                'color'       => '#E5343A',
                'text_color'  => '#ffffff',
                'link'        => '',
                'dismissible' => true,
            ],
        ];
    }

    public static function load(string $key = null)
    {
        $all = Cache::remember('bw_site_settings', 1800, function () {
            $defaults = self::defaults();
            $keys = ['sections','listing_filters','results_per_page','default_sort',
                     'card_fields','notif_templates','seo','announcement'];
            foreach ($keys as $k) {
                try {
                    $raw = Setting::where('type', 'site_'.$k)->value('data');
                    if ($raw) $defaults[$k] = json_decode($raw, true) ?? $defaults[$k];
                } catch (\Exception $e) {}
            }
            return $defaults;
        });
        return $key ? ($all[$key] ?? self::defaults()[$key] ?? null) : $all;
    }

    public static function clearCache(): void
    {
        Cache::forget('bw_site_settings');
    }

    public function index()
    {
        $cfg = self::load();
        return view('site-settings.index', compact('cfg'));
    }

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
        return response()->json(['success'=>true,'message'=>'Saved!']);
    }
}
