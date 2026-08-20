<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AmenitiesController extends Controller
{
    /** Default amenities (used as seed) */
    public static function defaults(): array
    {
        return [
            'Swimming Pool','Gym / Fitness','Car Parking','Lift / Elevator',
            'Power Backup','24/7 Security','Garden / Park','Children\'s Play Area',
            'High-Speed WiFi','Gas Pipeline','Sports Court','Clubhouse',
            'CCTV Surveillance','Intercom','Rainwater Harvesting','Visitor Parking',
            'Air Conditioning','Modular Kitchen','Fire Safety','Maintenance Staff',
        ];
    }

    /** Get all active amenities for frontend use */
    public static function getActive(): array
    {
        $amenities = DB::table('property_amenities')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        // If no amenities in DB yet, return defaults
        return !empty($amenities) ? $amenities : self::defaults();
    }

    /** Admin index page */
    public function index()
    {
        $amenities = DB::table('property_amenities')
            ->orderBy('sort_order')->orderBy('name')->get();

        // Seed defaults if empty
        if ($amenities->isEmpty()) {
            $now = now();
            $rows = array_map(fn($name, $i) => [
                'name'       => $name,
                'is_active'  => 1,
                'sort_order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ], self::defaults(), array_keys(self::defaults()));
            DB::table('property_amenities')->insert($rows);
            $amenities = DB::table('property_amenities')->orderBy('sort_order')->get();
        }

        $total  = $amenities->count();
        $active = $amenities->where('is_active', 1)->count();

        return view('amenities.index', compact('amenities', 'total', 'active'));
    }

    /** Add new amenity */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:property_amenities,name',
        ]);
        if ($v->fails()) {
            return response()->json(['error' => true, 'message' => $v->errors()->first()]);
        }

        $maxOrder = DB::table('property_amenities')->max('sort_order') ?? 0;

        DB::table('property_amenities')->insert([
            'name'       => trim($request->name),
            'is_active'  => 1,
            'sort_order' => $maxOrder + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['error' => false, 'message' => 'Amenity added successfully!']);
    }

    /** Toggle active/inactive */
    public function toggleStatus(Request $request)
    {
        DB::table('property_amenities')
            ->where('id', $request->id)
            ->update(['is_active' => $request->status, 'updated_at' => now()]);

        return response()->json([
            'error'   => false,
            'message' => 'Amenity ' . ($request->status ? 'enabled' : 'disabled') . '.',
        ]);
    }

    /** Rename amenity */
    public function update(Request $request, $id)
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:property_amenities,name,' . $id,
        ]);
        if ($v->fails()) {
            return response()->json(['error' => true, 'message' => $v->errors()->first()]);
        }

        DB::table('property_amenities')
            ->where('id', $id)
            ->update(['name' => trim($request->name), 'updated_at' => now()]);

        return response()->json(['error' => false, 'message' => 'Amenity updated!']);
    }

    /** Delete amenity */
    public function destroy($id)
    {
        DB::table('property_amenities')->where('id', $id)->delete();
        return response()->json(['error' => false, 'message' => 'Amenity deleted.']);
    }

    /** Update sort order */
    public function reorder(Request $request)
    {
        foreach ($request->order as $i => $id) {
            DB::table('property_amenities')
                ->where('id', $id)
                ->update(['sort_order' => $i + 1, 'updated_at' => now()]);
        }
        return response()->json(['error' => false, 'message' => 'Order saved!']);
    }
}
