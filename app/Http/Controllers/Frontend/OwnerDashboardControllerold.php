<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OwnerDashboardController extends Controller
{
    private function customer()
    {
        return session('bw_customer');
    }

    /** Main dashboard */
    public function index()
    {
        $cust = $this->customer();
        $custId = $cust['id'];

        $totalListings = DB::table('propertys')->where('added_by', $custId)->where('post_type', 1)->count();
        $activeListings = DB::table('propertys')->where('added_by', $custId)->where('post_type', 1)
            ->where('request_status', 'approved')->where('status', 1)->count();
        $pendingListings = DB::table('propertys')->where('added_by', $custId)->where('post_type', 1)
            ->where('request_status', 'pending')->count();

        $totalViews = DB::table('propertys')->where('added_by', $custId)->where('post_type', 1)
            ->sum('total_click');

        $myPropIds = DB::table('propertys')->where('added_by', $custId)->where('post_type', 1)
            ->pluck('id');

        $totalEnquiries = DB::table('interested_users')->whereIn('property_id', $myPropIds)->count();
        $newEnquiries   = DB::table('interested_users')->whereIn('property_id', $myPropIds)
            ->where('created_at', '>=', now()->subDay())->count();

        $savedCount = DB::table('favourites')->whereIn('property_id', $myPropIds)->count();

        // Recent enquiries with customer info
        $recentEnquiries = DB::table('interested_users as iu')
            ->join('customers as c', 'c.id', '=', 'iu.customer_id')
            ->join('propertys as p', 'p.id', '=', 'iu.property_id')
            ->whereIn('iu.property_id', $myPropIds)
            ->orderBy('iu.created_at', 'desc')
            ->limit(5)
            ->select('c.name', 'c.email', 'c.mobile', 'p.title', 'iu.created_at', 'iu.status')
            ->get();

        // Recent listings
        $recentListings = DB::table('propertys')->where('added_by', $custId)->where('post_type', 1)
            ->orderBy('created_at', 'desc')->limit(5)->get();

        // Weekly views (last 7 days) — approximate from total_click proportionally
        $weeklyViews = array_fill(0, 7, 0);

        // Active plan
        $activePlan = DB::table('user_packages as up')
            ->join('packages as pk', 'pk.id', '=', 'up.package_id')
            ->where('up.customer_id', $custId)
            ->where('up.status', 1)
            ->orderBy('up.created_at', 'desc')
            ->first();

        return view('frontend.owner.dashboard', compact(
            'cust', 'totalListings', 'activeListings', 'pendingListings',
            'totalViews', 'totalEnquiries', 'newEnquiries', 'savedCount',
            'recentEnquiries', 'recentListings', 'activePlan', 'weeklyViews'
        ));
    }

    /** My properties list */
    public function myProperties(Request $request)
    {
        $cust   = $this->customer();
        $custId = $cust['id'];

        $query = DB::table('propertys as p')
            ->leftJoin('categorys as c', 'c.id', '=', 'p.category_id')
            ->where('p.added_by', $custId)
            ->where('p.post_type', 1)
            ->select('p.*', 'c.name as category_name');

        if ($request->filled('search')) {
            $query->where('p.title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('p.propery_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('p.request_status', $request->status);
        }

        $properties = $query->orderBy('p.created_at', 'desc')->paginate(12);

        // Add gallery count
        foreach ($properties as &$prop) {
            $prop->gallery_count = DB::table('property_images')->where('propertys_id', $prop->id)->count();
            $prop->enquiry_count = DB::table('interested_users')->where('property_id', $prop->id)->count();
            $prop->saved_count   = DB::table('favourites')->where('property_id', $prop->id)->count();
        }

        return view('frontend.owner.my-properties', compact('cust', 'properties'));
    }

    /** Profile page */
    public function profile()
    {
        $cust = $this->customer();
        $full = Customer::find($cust['id']);
        return view('frontend.owner.profile', compact('cust', 'full'));
    }

    /** Update profile */
    public function updateProfile(Request $request)
    {
        $cust   = $this->customer();
        $custId = $cust['id'];

        $request->validate([
            'name'         => 'required|string|max:100',
            'mobile'       => 'required|string|max:15',
            'company_name' => 'nullable|string|max:150',
        ]);

        $data = [
            'name'         => $request->name,
            'mobile'       => $request->mobile,
            'phone_alt'    => $request->phone_alt,
            'city'         => $request->city,
            'state'        => $request->state,
            'address'      => $request->address ?? '',
            'about_me'     => $request->about_me,
            'company_name' => $request->company_name,
        ];

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/' . config('global.USER_IMG_PATH', 'user_img/')), $filename);
            $data['profile'] = $filename;
        }

        Customer::where('id', $custId)->update($data);

        // Refresh session
        $updated = Customer::find($custId);
        session(['bw_customer' => $updated->toArray()]);

        return back()->with('success', 'Profile updated successfully!');
    }

    /** Change password */
    public function changePassword(Request $request)
    {
        $cust = $this->customer();
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $customer = Customer::find($cust['id']);
        if (!Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $customer->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully!');
    }

    /** Enquiries page */
    public function enquiries(Request $request)
    {
        $cust   = $this->customer();
        $custId = $cust['id'];

        $myPropIds = DB::table('propertys')->where('added_by', $custId)->where('post_type', 1)->pluck('id');

        $enquiries = DB::table('interested_users as iu')
            ->join('customers as c', 'c.id', '=', 'iu.customer_id')
            ->join('propertys as p', 'p.id', '=', 'iu.property_id')
            ->whereIn('iu.property_id', $myPropIds)
            ->select('iu.*', 'c.name as buyer_name', 'c.email as buyer_email',
                     'c.mobile as buyer_mobile', 'p.title as property_title',
                     'p.slug_id as property_slug', 'p.city as property_city')
            ->orderBy('iu.created_at', 'desc')
            ->paginate(20);

        return view('frontend.owner.enquiries', compact('cust', 'enquiries'));
    }

    /** JSON: dashboard stats */
    public function statsApi()
    {
        $cust   = $this->customer();
        $custId = $cust['id'];
        $myPropIds = DB::table('propertys')->where('added_by', $custId)->pluck('id');

        return response()->json([
            'listings'  => DB::table('propertys')->where('added_by', $custId)->where('post_type', 1)->count(),
            'views'     => (int) DB::table('propertys')->where('added_by', $custId)->sum('total_click'),
            'enquiries' => DB::table('interested_users')->whereIn('property_id', $myPropIds)->count(),
            'saved'     => DB::table('favourites')->whereIn('property_id', $myPropIds)->count(),
        ]);
    }
}
