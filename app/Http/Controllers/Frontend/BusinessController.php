<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    public function index(Request $r)
    {
        $q = DB::table('businesses as b')
            ->leftJoin('business_categories as c', 'c.id', '=', 'b.business_category_id')
            ->where('b.status', 1)
            ->where('b.request_status', 'approved');

        // Location
        if ($r->filled('city')) {
            $city = trim($r->city);
            $q->where(function ($x) use ($city) {
                $x->where('b.city', 'like', '%'.$city.'%')
                  ->orWhere('b.locality', 'like', '%'.$city.'%')
                  ->orWhere('b.state', 'like', '%'.$city.'%');
            });
        }

        /*
         * Category compatibility:
         * New listing page: category=<business_category_id>
         * Existing home search: type=Restaurant
         * Older listing page: btype=Restaurant
         */
        $categoryId = $r->input('category');
        $categoryName = trim((string) ($r->input('type') ?: $r->input('btype')));

        if ($categoryId) {
            $q->where('b.business_category_id', $categoryId);
        } elseif ($categoryName !== '') {
            $q->where(function ($x) use ($categoryName) {
                $x->where('c.name', 'like', '%'.$categoryName.'%')
                  ->orWhere('b.business_type', 'like', '%'.$categoryName.'%');
            });
        }

        // Price compatibility
        $minPrice = $r->input('min_price', $r->input('price_min'));
        $maxPrice = $r->input('max_price', $r->input('price_max'));

        if ($minPrice !== null && $minPrice !== '') {
            $q->where('b.asking_price', '>=', (float) $minPrice);
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $q->where('b.asking_price', '<=', (float) $maxPrice);
        }

        $businesses = $q
            ->select('b.*', 'c.name as category_name')
            ->orderByDesc('b.is_featured')
            ->orderByDesc('b.id')
            ->paginate(12)
            ->withQueryString();

        $categories = DB::table('business_categories')
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('frontend.businesses.index', compact('businesses', 'categories'));
    }

    public function show($slug)
    {
        $business = DB::table('businesses as b')
            ->leftJoin('business_categories as c', 'c.id', '=', 'b.business_category_id')
            ->where('b.slug', $slug)
            ->where('b.status', 1)
            ->where('b.request_status', 'approved')
            ->select('b.*', 'c.name as category_name')
            ->first();

        if (!$business) abort(404);

        DB::table('businesses')->where('id', $business->id)->increment('views');

        // Keep current page value correct after increment.
        $business->views = ((int) $business->views) + 1;

        $images = DB::table('business_images')
            ->where('business_id', $business->id)
            ->orderBy('sort_order')
            ->get();

        return view('frontend.businesses.show', compact('business', 'images'));
    }

    public function enquiry(Request $r)
    {
        // property_id compatibility is retained only for an older cached frontend.
        if (!$r->filled('business_id') && $r->filled('property_id')) {
            $r->merge(['business_id' => $r->property_id]);
        }

        $d = $r->validate([
            'business_id' => 'required|integer|exists:businesses,id',
            'name' => 'required|string|max:120',
            'mobile' => 'required|string|max:30',
            'email' => 'nullable|email|max:160',
            'buyer_type' => 'nullable|string|max:60',
            'investment_budget' => 'nullable|string|max:80',
            'message' => 'nullable|string|max:1000',
        ]);

        $d['buyer_customer_id'] = session('bw_customer.id') ?? null;
        $d['status'] = 'new';
        $d['created_at'] = now();
        $d['updated_at'] = now();

        DB::table('business_enquiries')->insert($d);

        if ($r->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your enquiry has been sent to the business seller.'
            ]);
        }

        return back()->with('success', 'Your enquiry has been sent to the business seller.');
    }
}
