<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SearchSettingsController extends Controller
{
    /* ── Defaults (used when dynamic is OFF) ────────────────── */
    public static function defaults(): array
    {
        return [
            'enabled'          => false,
            'tabs'             => [
                ['slug'=>'buy',        'label'=>'Buy',        'icon'=>'fa-house',  'active'=>true],
                ['slug'=>'rent',       'label'=>'Rent',       'icon'=>'fa-key',    'active'=>true],
                ['slug'=>'commercial', 'label'=>'Commercial', 'icon'=>'fa-store',  'active'=>true],
                ['slug'=>'plots',      'label'=>'Plots',      'icon'=>'fa-map',    'active'=>true],
                ['slug'=>'projects',   'label'=>'Projects',   'icon'=>'fa-city',   'active'=>true],
            ],
            'buy_subtypes'     => [
                ['label'=>'Full House', 'slug'=>'fullhouse', 'active'=>true],
                ['label'=>'Land / Plot','slug'=>'landplot',  'active'=>true],
            ],
            'rent_subtypes'    => [
                ['label'=>'Full House', 'slug'=>'fullhouse', 'active'=>true],
                ['label'=>'PG / Hostel','slug'=>'pghostel',  'active'=>true],
                ['label'=>'Flatmates',  'slug'=>'flatmates', 'active'=>true],
            ],
            'bhk_options'      => ['1 BHK','2 BHK','3 BHK','4 BHK','5+ BHK'],
            'prop_statuses'    => ['Ready to Move','Under Construction','New Launch'],
            'commercial_types' => ['Office','Co-working Space','Shop / Showroom','Warehouse','Factory / Industrial'],
            'budget_buy'       => [
                ['label'=>'Under ₹25L', 'min'=>'0',        'max'=>'2500000'],
                ['label'=>'₹25L–₹50L',  'min'=>'2500000',  'max'=>'5000000'],
                ['label'=>'₹50L–₹1Cr',  'min'=>'5000000',  'max'=>'10000000'],
                ['label'=>'₹1Cr–₹2Cr',  'min'=>'10000000', 'max'=>'20000000'],
                ['label'=>'Above ₹2Cr', 'min'=>'20000000', 'max'=>''],
            ],
            'budget_rent'      => [
                ['label'=>'Under ₹10k', 'min'=>'0',     'max'=>'10000'],
                ['label'=>'₹10k–₹25k',  'min'=>'10000', 'max'=>'25000'],
                ['label'=>'₹25k–₹50k',  'min'=>'25000', 'max'=>'50000'],
                ['label'=>'Above ₹50k', 'min'=>'50000', 'max'=>''],
            ],
        ];
    }

    /* ── Load settings from DB ──────────────────────────────── */
    public static function load(): array
    {
        $enabled = Setting::where('type','search_dynamic_enabled')->value('data');
        if (!$enabled || $enabled == '0') {
            return self::defaults();
        }

        $keys = ['tabs','buy_subtypes','rent_subtypes','bhk_options',
                 'prop_statuses','commercial_types','budget_buy','budget_rent'];
        $data = ['enabled' => true];
        foreach ($keys as $key) {
            $raw = Setting::where('type','sw_'.$key)->value('data');
            $data[$key] = $raw ? json_decode($raw, true) : self::defaults()[$key];
        }
        return $data;
    }

    /* ── Admin index page ───────────────────────────────────── */
    public function index()
    {
        $cfg = self::load();
        return view('search-settings.index', compact('cfg'));
    }

    /* ── Save master toggle ─────────────────────────────────── */
    public function toggleDynamic(Request $request)
    {
        Setting::updateOrCreate(
            ['type' => 'search_dynamic_enabled'],
            ['data' => $request->enabled ? '1' : '0']
        );
        return response()->json(['success'=>true,'message'=>'Search mode updated!']);
    }

    /* ── Save any section ───────────────────────────────────── */
    public function save(Request $request)
    {
        $key   = $request->key;
        $value = $request->value;

        $allowed = ['tabs','buy_subtypes','rent_subtypes','bhk_options',
                    'prop_statuses','commercial_types','budget_buy','budget_rent'];

        if (!in_array($key, $allowed)) {
            return response()->json(['success'=>false,'message'=>'Invalid key.']);
        }

        Setting::updateOrCreate(
            ['type' => 'sw_'.$key],
            ['data' => json_encode($value)]
        );

        return response()->json(['success'=>true,'message'=>ucwords(str_replace('_',' ',$key)).' saved!']);
    }

    /* ── Reset to defaults ──────────────────────────────────── */
    public function reset()
    {
        $keys = ['tabs','buy_subtypes','rent_subtypes','bhk_options',
                 'prop_statuses','commercial_types','budget_buy','budget_rent'];
        foreach ($keys as $key) {
            Setting::where('type','sw_'.$key)->delete();
        }
        Setting::where('type','search_dynamic_enabled')->delete();
        return response()->json(['success'=>true,'message'=>'Search settings reset to defaults!']);
    }
}
