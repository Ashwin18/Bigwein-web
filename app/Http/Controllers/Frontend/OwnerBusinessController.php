<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OwnerBusinessController extends Controller
{
    private function owner()
    {
        $s = session('bw_customer');
        $id = $s['id'] ?? $s['customer_id'] ?? null;
        if (!$id && !empty($s['email'])) {
            $id = DB::table('customers')->where('email', $s['email'])->value('id');
        }
        return $id ? DB::table('customers')->where('id', $id)->first() : null;
    }

    private function gate()
    {
        $o = $this->owner();
        if (!$o) return redirect('/owner/login');

        if (strtolower((string)($o->kyc_status ?? 'pending')) !== 'approved') {
            return redirect('/owner/kyc')->with('error', 'Complete KYC approval before listing a business.');
        }
        return null;
    }

    private function categories()
    {
        return DB::table('business_categories')
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function validateBusiness(Request $request, bool $draft = false): void
    {
        $rules = [
            'title' => $draft ? 'nullable|string|max:180' : 'required|string|max:180',
            'business_category_id' => $draft ? 'nullable|integer' : 'required|integer',
            'business_status' => $draft ? 'nullable|in:running,temporarily_closed,new_setup,franchise_resale' : 'required|in:running,temporarily_closed,new_setup,franchise_resale',
            'description' => $draft ? 'nullable|string|max:5000' : 'required|string|max:5000',
            'asking_price' => $draft ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'city' => $draft ? 'nullable|string|max:100' : 'required|string|max:100',
            'state' => $draft ? 'nullable|string|max:100' : 'required|string|max:100',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'category_details' => 'nullable|array',
        ];
        $request->validate($rules);
    }

    private function basePayload(Request $request): array
    {
        return [
            'business_category_id' => $request->business_category_id ?: null,
            'title' => trim((string)$request->title) ?: 'Untitled Business Draft',
            'business_type' => $request->business_type,
            'business_status' => $request->business_status ?: 'running',
            'established_year' => $request->established_year ?: null,
            'employees' => $request->employees ?: null,
            'description' => $request->description,
            'reason_for_sale' => $request->reason_for_sale,
            'asking_price' => $request->asking_price ?: 0,
            'negotiable' => $request->boolean('negotiable'),
            'monthly_revenue' => $request->monthly_revenue ?: null,
            'monthly_expense' => $request->monthly_expense ?: null,
            'monthly_profit' => $request->monthly_profit ?: null,
            'inventory_value' => $request->inventory_value ?: null,
            'financial_visibility' => $request->financial_visibility ?: 'verified_buyers',
            'premises_type' => $request->premises_type ?: null,
            'monthly_rent' => $request->monthly_rent ?: null,
            'lease_months_remaining' => $request->lease_months_remaining ?: null,
            'built_up_area' => $request->built_up_area ?: null,
            'city' => $request->city,
            'state' => $request->state,
            'locality' => $request->locality,
            'address' => $request->address,
            'is_confidential' => $request->boolean('is_confidential'),
            'assets_included' => json_encode(array_values(array_filter($request->input('assets', [])))),
            'category_details' => json_encode(array_filter($request->input('category_details', []), fn($v) => $v !== null && $v !== '')),
            'updated_at' => now(),
        ];
    }

    private function saveUploads(Request $request, int $id): void
    {
        $dir = public_path('images/businesses/'.$id);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if ($request->hasFile('cover_image')) {
            $old = DB::table('businesses')->where('id', $id)->value('cover_image');
            if ($old && is_file($dir.'/'.$old)) @unlink($dir.'/'.$old);

            $f = $request->file('cover_image');
            $n = 'cover_'.time().'.'.$f->getClientOriginalExtension();
            $f->move($dir, $n);
            DB::table('businesses')->where('id', $id)->update(['cover_image' => $n]);
        }

        $sort = (int) DB::table('business_images')->where('business_id', $id)->max('sort_order');
        foreach ($request->file('gallery', []) as $i => $f) {
            $n = 'gallery_'.time().'_'.$i.'.'.$f->getClientOriginalExtension();
            $f->move($dir, $n);
            DB::table('business_images')->insert([
                'business_id' => $id,
                'image' => $n,
                'sort_order' => $sort + $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($request->file('documents', []) as $type => $f) {
            if (!$f) continue;

            $old = DB::table('business_documents')
                ->where('business_id', $id)
                ->where('document_type', $type)
                ->first();

            if ($old && is_file($dir.'/'.$old->file_name)) @unlink($dir.'/'.$old->file_name);
            if ($old) DB::table('business_documents')->where('id', $old->id)->delete();

            $n = 'doc_'.Str::slug($type).'_'.time().'.'.$f->getClientOriginalExtension();
            $f->move($dir, $n);
            DB::table('business_documents')->insert([
                'business_id' => $id,
                'document_type' => $type,
                'file_name' => $n,
                'admin_only' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ((array)$request->input('remove_images', []) as $imageId) {
            $img = DB::table('business_images')
                ->where('id', $imageId)
                ->where('business_id', $id)
                ->first();
            if (!$img) continue;
            if (is_file($dir.'/'.$img->image)) @unlink($dir.'/'.$img->image);
            DB::table('business_images')->where('id', $img->id)->delete();
        }
    }

    public function index()
    {
        $owner = $this->owner();
        if (!$owner) return redirect('/owner/login');

        $rows = DB::table('businesses as b')
            ->leftJoin('business_categories as c', 'c.id', '=', 'b.business_category_id')
            ->where('b.customer_id', $owner->id)
            ->select('b.*', 'c.name as category_name')
            ->orderByDesc('b.id')
            ->paginate(12);

        return view('frontend.owner.business.index', compact('owner', 'rows'));
    }

    public function create()
    {
        if ($r = $this->gate()) return $r;
        $owner = $this->owner();
        $categories = $this->categories();
        $business = null;
        $images = collect();
        $documents = collect();

        return view('frontend.owner.business.create', compact('owner', 'categories', 'business', 'images', 'documents'));
    }

    public function store(Request $request)
    {
        if ($r = $this->gate()) return $r;
        $owner = $this->owner();

        $isDraft = $request->input('action') === 'draft';
        $this->validateBusiness($request, $isDraft);

        $payload = $this->basePayload($request);
        $payload += [
            'customer_id' => $owner->id,
            'reference_no' => 'BIZ-'.date('ymd').'-'.strtoupper(Str::random(5)),
            'slug' => Str::slug($payload['title']).'-'.Str::lower(Str::random(6)),
            'request_status' => $isDraft ? 'draft' : 'pending',
            'status' => 0,
            'submitted_at' => $isDraft ? null : now(),
            'created_at' => now(),
        ];

        $id = DB::table('businesses')->insertGetId($payload);
        $this->saveUploads($request, $id);

        return redirect('/owner/my-businesses')
            ->with('success', $isDraft ? 'Business draft saved.' : 'Business submitted for admin approval.');
    }

    public function edit($id)
    {
        if ($r = $this->gate()) return $r;
        $owner = $this->owner();

        $business = DB::table('businesses')
            ->where('id', $id)
            ->where('customer_id', $owner->id)
            ->first();

        if (!$business) abort(404);

        if (in_array($business->request_status, ['pending', 'approved'], true)) {
            return redirect('/owner/my-businesses')
                ->with('error', 'This business cannot be edited while it is under review or live.');
        }

        $categories = $this->categories();
        $images = DB::table('business_images')->where('business_id', $id)->orderBy('sort_order')->get();
        $documents = DB::table('business_documents')->where('business_id', $id)->get();

        return view('frontend.owner.business.create', compact('owner', 'categories', 'business', 'images', 'documents'));
    }

    public function update(Request $request, $id)
    {
        if ($r = $this->gate()) return $r;
        $owner = $this->owner();

        $business = DB::table('businesses')
            ->where('id', $id)
            ->where('customer_id', $owner->id)
            ->first();

        if (!$business) abort(404);

        if (in_array($business->request_status, ['pending', 'approved'], true)) {
            return redirect('/owner/my-businesses')
                ->with('error', 'This business cannot be edited while it is under review or live.');
        }

        $isDraft = $request->input('action') === 'draft';
        $this->validateBusiness($request, $isDraft);

        $payload = $this->basePayload($request);
        $payload['request_status'] = $isDraft ? 'draft' : 'pending';
        $payload['status'] = 0;
        $payload['submitted_at'] = $isDraft ? null : now();
        $payload['admin_remarks'] = null;

        DB::table('businesses')->where('id', $id)->update($payload);
        $this->saveUploads($request, (int)$id);

        return redirect('/owner/my-businesses')
            ->with('success', $isDraft ? 'Business draft updated.' : 'Business resubmitted for admin approval.');
    }

    public function enquiries()
    {
        $owner = $this->owner();
        if (!$owner) return redirect('/owner/login');

        $rows = DB::table('business_enquiries as e')
            ->join('businesses as b', 'b.id', '=', 'e.business_id')
            ->where('b.customer_id', $owner->id)
            ->select('e.*', 'b.title as business_title', 'b.reference_no')
            ->orderByDesc('e.id')
            ->paginate(20);

        return view('frontend.owner.business.enquiries', compact('owner', 'rows'));
    }
}