<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerSubscriptionController extends Controller
{
    private function customer()
    {
        return session('bw_customer');
    }

    /** Show subscription plans */
    public function index()
    {
        $cust    = $this->customer();
        $custId  = $cust['id'];
        $packages = DB::table('packages')->where('status', 1)->orderBy('price', 'asc')->get();

        $activePlan = DB::table('user_packages as up')
            ->join('packages as pk', 'pk.id', '=', 'up.package_id')
            ->where('up.customer_id', $custId)
            ->where('up.status', 1)
            ->orderBy('up.created_at', 'desc')
            ->select('up.*', 'pk.name as plan_name', 'pk.price as plan_price')
            ->first();

        return view('frontend.owner.subscription', compact('cust', 'packages', 'activePlan'));
    }

    /** Subscribe (placeholder for payment gateway integration) */
    public function subscribe(Request $request, $packageId)
    {
        $cust    = $this->customer();
        $custId  = $cust['id'];
        $package = DB::table('packages')->where('id', $packageId)->firstOrFail();

        // Deactivate old plan
        DB::table('user_packages')->where('customer_id', $custId)->update(['status' => 0]);

        // Create new plan record
        DB::table('user_packages')->insert([
            'customer_id' => $custId,
            'package_id'  => $packageId,
            'status'      => 1,
            'start_date'  => now(),
            'end_date'    => now()->addDays($package->duration ?? 30),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Update property count limit (conceptual — admin reviews properties)
        return response()->json([
            'success' => true,
            'message' => 'Subscribed to ' . $package->name . ' plan successfully!',
        ]);
    }
}
