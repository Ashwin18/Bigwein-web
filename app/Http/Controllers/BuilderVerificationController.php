<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuilderVerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'submitted');

        $q = DB::table('builder_profiles as b')
            ->join('customers as c', 'c.id', '=', 'b.customer_id')
            ->where('c.owner_type', 'builder')
            ->select(
                'b.*',
                'c.name as owner_name',
                'c.email',
                'c.mobile',
                'c.kyc_status as personal_kyc_status'
            );

        if ($status !== 'all') $q->where('b.status', $status);

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function($x) use ($s) {
                $x->where('b.company_name','like','%'.$s.'%')
                  ->orWhere('c.name','like','%'.$s.'%')
                  ->orWhere('c.email','like','%'.$s.'%')
                  ->orWhere('c.mobile','like','%'.$s.'%');
            });
        }

        $rows = $q->orderByDesc('b.submitted_at')->paginate(20)->withQueryString();

        $counts = [
            'submitted' => DB::table('builder_profiles')->whereIn('status',['submitted','under_review'])->count(),
            'approved' => DB::table('builder_profiles')->where('status','approved')->count(),
            'changes_requested' => DB::table('builder_profiles')->where('status','changes_requested')->count(),
            'rejected' => DB::table('builder_profiles')->where('status','rejected')->count(),
        ];

        return view('builder-management.verification', compact('rows','counts','status'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,changes_requested,rejected',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if (in_array($request->status, ['changes_requested','rejected'], true) && !$request->filled('remarks')) {
            return back()->with('error', 'Please enter remarks before requesting changes or rejecting.');
        }

        $profile = DB::table('builder_profiles')->where('id', $id)->first();
        if (!$profile) return back()->with('error', 'Builder verification record not found.');

        DB::table('builder_profiles')->where('id', $id)->update([
            'status' => $request->status,
            'admin_remarks' => $request->remarks,
            'approved_at' => $request->status === 'approved' ? now() : null,
            'approved_by' => $request->status === 'approved' ? auth()->id() : null,
            'updated_at' => now(),
        ]);

        return back()->with('success',
            $request->status === 'approved'
                ? 'Builder / Developer company verification approved.'
                : ($request->status === 'changes_requested'
                    ? 'Changes requested from Builder / Developer.'
                    : 'Builder / Developer verification rejected.')
        );
    }
}