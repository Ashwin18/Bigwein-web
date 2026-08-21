<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class OwnerKycController extends Controller
{
    private function ownerId(Request $request): ?int
    {
        $session = $request->session()->get('bw_customer', []);
        $id = $session['id'] ?? $session['customer_id'] ?? null;
        if ($id) {
            return (int) $id;
        }

        if (!empty($session['email'])) {
            $id = DB::table('customers')->where('email', $session['email'])->value('id');
            if ($id) return (int) $id;
        }

        if (!empty($session['mobile'])) {
            $id = DB::table('customers')->where('mobile', $session['mobile'])->value('id');
            if ($id) return (int) $id;
        }

        return null;
    }

    private function ownerOrRedirect(Request $request)
    {
        $id = $this->ownerId($request);
        if (!$id) return [null, redirect('/owner/login')->with('error', 'Please login to continue.')];

        $owner = DB::table('customers')->where('id', $id)->whereNotNull('owner_type')->first();
        if (!$owner) return [null, redirect('/owner/login')->with('error', 'Owner account not found.')];

        return [$owner, null];
    }

    public function index(Request $request)
    {
        [$owner, $redirect] = $this->ownerOrRedirect($request);
        if ($redirect) return $redirect;

        $kyc = DB::table('customer_kyc')->where('customer_id', $owner->id)->latest('id')->first();
        return view('frontend.owner.kyc', compact('owner', 'kyc'));
    }

    public function submit(Request $request)
    {
        [$owner, $redirect] = $this->ownerOrRedirect($request);
        if ($redirect) return $redirect;

        $current = DB::table('customer_kyc')->where('customer_id', $owner->id)->latest('id')->first();
        $currentStatus = strtolower((string)($owner->kyc_status ?? ($current->status ?? 'pending')));

        // V2.1 — KYC is immutable while it is being reviewed.
        // The owner may edit only before first submission, after rejection,
        // or when the admin explicitly requests changes.
        if (in_array($currentStatus, ['submitted', 'under_review'], true)) {
            return back()->with('error', 'Your KYC is under review and cannot be edited until the admin requests changes.');
        }

        if ($currentStatus === 'approved') {
            return back()->with('error', 'Your KYC is already approved. Contact admin if verified details need to be changed.');
        }

        $validator = Validator::make($request->all(), [
            'aadhaar_number' => ['required', 'regex:/^[0-9]{12}$/'],
            'aadhaar_front'  => [$current && !empty($current->aadhaar_front) ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'aadhaar_back'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'aadhaar_number.regex' => 'Aadhaar number must contain exactly 12 digits.',
            'aadhaar_front.required' => 'Please upload the front side of the Aadhaar card.',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $duplicate = DB::table('customer_kyc')
            ->where('aadhaar_number', $request->aadhaar_number)
            ->where('customer_id', '!=', $owner->id)
            ->exists();
        if ($duplicate) return back()->withErrors(['aadhaar_number' => 'This Aadhaar number is already linked with another account.'])->withInput();

        $folder = public_path('images/customer_kyc/'.$owner->id);
        if (!File::isDirectory($folder)) File::makeDirectory($folder, 0755, true);

        $front = $current->aadhaar_front ?? null;
        $back  = $current->aadhaar_back ?? null;

        if ($request->hasFile('aadhaar_front')) {
            $f = $request->file('aadhaar_front');
            $front = 'aadhaar_front_'.time().'.'.$f->getClientOriginalExtension();
            $f->move($folder, $front);
        }
        if ($request->hasFile('aadhaar_back')) {
            $f = $request->file('aadhaar_back');
            $back = 'aadhaar_back_'.time().'.'.$f->getClientOriginalExtension();
            $f->move($folder, $back);
        }

        $data = [
            'customer_id' => $owner->id,
            'aadhaar_number' => $request->aadhaar_number,
            'aadhaar_front' => $front,
            'aadhaar_back' => $back,
            'status' => 'submitted',
            'remarks' => null,
            'submitted_at' => now(),
            'updated_at' => now(),
        ];

        if ($current) {
            DB::table('customer_kyc')->where('id', $current->id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('customer_kyc')->insert($data);
        }

        DB::table('customers')->where('id', $owner->id)->update([
            'aadhaar_number' => $request->aadhaar_number,
            'kyc_status' => 'submitted',
            'kyc_reject_reason' => null,
            'updated_at' => now(),
        ]);

        return redirect('/owner/dashboard')->with('success', 'KYC submitted successfully. You can post properties after admin approval.');
    }

    public function skip(Request $request)
    {
        [$owner, $redirect] = $this->ownerOrRedirect($request);
        if ($redirect) return $redirect;

        if (empty($owner->kyc_status) || $owner->kyc_status === 'not_submitted') {
            DB::table('customers')->where('id', $owner->id)->update(['kyc_status' => 'pending', 'updated_at' => now()]);
        }
        return redirect('/owner/dashboard')->with('success', 'You can complete KYC later from Dashboard or My Profile. KYC approval is required before posting.');
    }

    public function adminIndex(Request $request)
    {
        if (!has_permissions('read', 'customer')) {
            return redirect()->back()->with('error', trans(PERMISSION_ERROR_MSG));
        }

        $status = $request->get('status', 'submitted');
        $q = DB::table('customer_kyc as k')
            ->join('customers as c', 'c.id', '=', 'k.customer_id')
            ->whereNotNull('c.owner_type')
            ->select('k.*','c.name','c.email','c.mobile','c.owner_type','c.company_name','c.city','c.state');

        if ($status && $status !== 'all') {
            if ($status === 'submitted') {
                $q->whereIn('k.status', ['submitted','under_review']);
            } else {
                $q->where('k.status', $status);
            }
        }

        if ($request->filled('owner_type')) {
            $q->where('c.owner_type', $request->owner_type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function($x) use ($s) {
                $x->where('c.name','like','%'.$s.'%')->orWhere('c.email','like','%'.$s.'%')->orWhere('c.mobile','like','%'.$s.'%');
            });
        }

        $rows = $q->orderByDesc('k.submitted_at')->paginate(20)->withQueryString();
        $countFor = fn (array $statuses) => DB::table('customer_kyc as k')
            ->join('customers as c', 'c.id', '=', 'k.customer_id')
            ->whereNotNull('c.owner_type')
            ->whereIn('k.status', $statuses)
            ->count();
        $counts = [
            'submitted' => $countFor(['submitted','under_review']),
            'approved' => $countFor(['approved']),
            'changes_requested' => $countFor(['changes_requested']),
            'rejected' => $countFor(['rejected']),
        ];
        return view('owner-management.kyc', compact('rows','counts','status'));
    }

    public function adminUpdate(Request $request, $id)
    {
        if (!has_permissions('update', 'customer')) {
            return redirect()->back()->with('error', trans(PERMISSION_ERROR_MSG));
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,changes_requested',
            'remarks' => 'nullable|string|max:500',
        ]);
        if (in_array($request->status, ['rejected', 'changes_requested'], true) && !$request->filled('remarks')) {
            return back()->with('error', $request->status === 'changes_requested'
                ? 'Please describe the changes required from the owner.'
                : 'Please enter a rejection reason.');
        }

        $kyc = DB::table('customer_kyc')->where('id', $id)->first();
        if (!$kyc) return back()->with('error', 'KYC record not found.');

        DB::transaction(function() use ($request, $kyc) {
            DB::table('customer_kyc')->where('id', $kyc->id)->update([
                'status' => $request->status,
                'remarks' => $request->remarks,
                'approved_by' => auth()->id(),
                'approved_at' => $request->status === 'approved' ? now() : null,
                'updated_at' => now(),
            ]);

            DB::table('customers')->where('id', $kyc->customer_id)->update([
                'aadhaar_number' => $kyc->aadhaar_number,
                'kyc_status' => $request->status,
                'kyc_verified_at' => $request->status === 'approved' ? now() : null,
                'kyc_verified_by' => $request->status === 'approved' ? auth()->id() : null,
                // Existing column is retained for backwards compatibility and is
                // also used to show admin-requested corrections to the owner.
                'kyc_reject_reason' => in_array($request->status, ['rejected', 'changes_requested'], true)
                    ? $request->remarks
                    : null,
                'updated_at' => now(),
            ]);
        });

                $message = $request->status === 'changes_requested'
            ? 'Changes requested from the owner. KYC editing has been reopened.'
            : 'KYC '.$request->status.' successfully.';

        return back()->with('success', $message);
    }
}
