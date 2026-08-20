<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\RejectReason;
use App\Models\Notifications;
use App\Models\Usertokens;
use App\Services\HelperService;

class PropertyApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        if (!in_array($status, ['pending','approved','rejected'], true)) $status = 'pending';

        $pending  = DB::table('propertys')->where('post_type',1)->where('request_status','pending')->count();
        $approved = DB::table('propertys')->where('post_type',1)->where('request_status','approved')->count();
        $rejected = DB::table('propertys')->where('post_type',1)->where('request_status','rejected')->count();
        $categories = DB::table('categories')->where('status',1)->orderBy('sequence')->get();

        $query = DB::table('propertys as p')
            ->leftJoin('customers as c','c.id','=','p.added_by')
            ->leftJoin('categories as cat','cat.id','=','p.category_id')
            ->where('p.post_type',1)
            ->where('p.request_status',$status);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function($sub) use ($q) {
                $sub->where('p.title','like',"%{$q}%")
                    ->orWhere('p.city','like',"%{$q}%")
                    ->orWhere('c.name','like',"%{$q}%")
                    ->orWhere('c.email','like',"%{$q}%");
            });
        }
        if ($request->filled('category_id')) $query->where('p.category_id',$request->category_id);

        $items = $query->select(
                'p.id','p.title','p.city','p.state','p.price','p.propery_type','p.request_status',
                'p.title_image','p.created_at','p.sub_type','p.commercial_type','p.total_area','p.post_type',
                'c.name as owner_name','c.email as owner_email','c.mobile as owner_mobile',
                'c.owner_type','c.company_name','cat.category as category_name'
            )
            ->orderByDesc('p.created_at')
            ->paginate(12)
            ->withQueryString();

        foreach ($items as $item) {
            $item->gallery_count = DB::table('property_images')->where('propertys_id',$item->id)->count();
            $item->document_count = DB::table('properties_documents')->where('property_id',$item->id)->count();
        }

        return view('property-approval.index', compact('pending','approved','rejected','categories','items','status'));
    }

    public function approvalList(Request $request)
    {
        // Kept for backwards compatibility with any older JS still calling this endpoint.
        $request->merge(['status' => $request->input('request_status','pending')]);
        $status = $request->input('request_status','pending');
        $offset = (int)$request->input('offset',0);
        $limit  = (int)$request->input('limit',10);
        $query = DB::table('propertys as p')
            ->leftJoin('customers as c','c.id','=','p.added_by')
            ->leftJoin('categories as cat','cat.id','=','p.category_id')
            ->where('p.post_type',1)->where('p.request_status',$status);
        if ($request->filled('search')) {
            $s=$request->search;
            $query->where(function($q) use($s){$q->where('p.title','LIKE',"%$s%")->orWhere('p.city','LIKE',"%$s%")->orWhere('c.name','LIKE',"%$s%");});
        }
        if ($request->filled('category_id')) $query->where('p.category_id',$request->category_id);
        $total=$query->count();
        $rows=$query->select('p.*','c.name as owner_name','c.email as owner_email','c.mobile as owner_mobile','cat.category as category_name')
            ->orderByDesc('p.created_at')->offset($offset)->limit($limit)->get();
        return response()->json(['total'=>$total,'rows'=>$rows]);
    }

    public function approve(Request $request)
    {
        $request->validate(['id'=>'required|integer']);
        $prop = Property::with('customer')->where('id',$request->id)->where('post_type',1)->first();
        if (!$prop) return response()->json(['error'=>true,'message'=>'Property not found.'],404);

        DB::transaction(function() use ($prop) {
            $prop->request_status='approved';
            $prop->status=1;
            $prop->save();
            RejectReason::where('property_id',$prop->id)->delete();
        });
        $this->notifyOwner($prop, 'approved', null);
        try { HelperService::AlertUserForNewListing($prop->id); } catch (\Throwable $e) {}
        return response()->json(['error'=>false,'message'=>'Property approved and published successfully.']);
    }

    public function reject(Request $request)
    {
        $request->validate(['id'=>'required|integer','reason'=>'required|string|max:300']);
        $prop = Property::with('customer')->where('id',$request->id)->where('post_type',1)->first();
        if (!$prop) return response()->json(['error'=>true,'message'=>'Property not found.'],404);

        DB::transaction(function() use ($prop,$request) {
            $prop->request_status='rejected';
            $prop->status=0;
            $prop->save();
            RejectReason::updateOrCreate(['property_id'=>$prop->id],['reason'=>$request->reason]);
        });
        $this->notifyOwner($prop, 'rejected', $request->reason);
        return response()->json(['error'=>false,'message'=>'Property rejected. The reason has been saved.']);
    }


    public function detail($id)
    {
        $id = (int) $id;

        $prop = DB::table('propertys as p')
            ->leftJoin('customers as c', 'c.id', '=', 'p.added_by')
            ->leftJoin('categories as cat', 'cat.id', '=', 'p.category_id')
            ->where('p.id', $id)
            ->select(
                'p.*',
                'c.name as owner_name',
                'c.email as owner_email',
                'c.mobile as owner_mobile',
                'c.owner_type',
                'c.company_name',
                'cat.category as category_name'
            )
            ->first();

        if (!$prop) {
            abort(404, 'Property not found.');
        }

        $prop->title_image_url = null;
        $prop->title_image_exists = false;

        if (!empty($prop->title_image)) {
            $titleBase = trim((string) config('global.PROPERTY_TITLE_IMG_PATH', 'property_title_img/'), '/');
            $titleCandidates = [
                'images/'.$titleBase.'/'.$prop->title_image,
                'images/'.$titleBase.$prop->title_image,
                'images/'.$prop->title_image,
            ];

            foreach ($titleCandidates as $candidate) {
                $candidate = preg_replace('#/+#', '/', $candidate);
                if (file_exists(public_path($candidate))) {
                    $prop->title_image_url = url($candidate);
                    $prop->title_image_exists = true;
                    break;
                }
            }
        }

        $gallery = DB::table('property_images')
            ->where('propertys_id', $id)
            ->orderBy('id')
            ->get();

        foreach ($gallery as $img) {
            $img->image_url = null;
            $img->exists = false;

            if (empty($img->image)) {
                continue;
            }

            $galleryBase = trim((string) config('global.PROPERTY_GALLERY_IMG_PATH', 'property_gallery_img/'), '/');
            $candidates = [
                'images/'.$galleryBase.'/'.$id.'/'.$img->image,
                'images/'.$galleryBase.'/'.$img->image,
                'images/property_gallery_img/'.$id.'/'.$img->image,
                'images/property_gallery_img/'.$img->image,
            ];

            foreach (array_unique($candidates) as $candidate) {
                $candidate = preg_replace('#/+#', '/', $candidate);
                if (file_exists(public_path($candidate))) {
                    $img->image_url = url($candidate);
                    $img->exists = true;
                    break;
                }
            }
        }

        $parameters = DB::table('assign_parameters as ap')
            ->leftJoin('parameters as par', 'par.id', '=', 'ap.parameter_id')
            ->where(function ($q) use ($id) {
                $q->where('ap.property_id', $id)
                  ->orWhere('ap.modal_id', $id);
            })
            ->select(
                DB::raw("COALESCE(par.name, CONCAT('Parameter #', ap.parameter_id)) as name"),
                'ap.value'
            )
            ->get();

        $facilities = DB::table('assigned_outdoor_facilities as af')
            ->leftJoin('outdoor_facilities as of', 'of.id', '=', 'af.facility_id')
            ->where('af.property_id', $id)
            ->select(
                DB::raw("COALESCE(of.name, CONCAT('Facility #', af.facility_id)) as name"),
                'af.distance'
            )
            ->get();

        $documents = DB::table('properties_documents')
            ->where('property_id', $id)
            ->orderBy('id')
            ->get();

        foreach ($documents as $document) {
            $document->document_url = null;
            $document->exists = false;

            if (empty($document->name)) {
                continue;
            }

            $documentBase = trim((string) config('global.PROPERTY_DOCUMENT_PATH', 'property_documents/'), '/');
            $candidates = [
                'images/'.$documentBase.'/'.$id.'/'.$document->name,
                'images/'.$documentBase.'/'.$document->name,
            ];

            foreach (array_unique($candidates) as $candidate) {
                $candidate = preg_replace('#/+#', '/', $candidate);
                if (file_exists(public_path($candidate))) {
                    $document->document_url = url($candidate);
                    $document->exists = true;
                    break;
                }
            }
        }

        $rejectReason = DB::table('reject_reasons')
            ->where('property_id', $id)
            ->latest('id')
            ->value('reason');

        return view('property-approval.detail', compact(
            'prop',
            'gallery',
            'parameters',
            'facilities',
            'documents',
            'rejectReason'
        ));
    }

    private function notifyOwner($property, string $status, ?string $reason): void
    {
        try {
            $customer = $property->customer;
            if (!$customer) return;
            $message = $status === 'approved' ? 'Your Property Post was Approved by Administrator.' : 'Your Property Post was Rejected by Administrator.';
            Notifications::create([
                'title' => $property->title.' Property Updated',
                'message' => $message,
                'image' => '', 'type' => 1, 'send_type' => 0,
                'customers_id' => $customer->id, 'propertys_id' => $property->id
            ]);
        } catch (\Throwable $e) {}
    }
}
