<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OwnerManagementController extends Controller
{
    public function index()
    {
        $totalOwners   = Customer::whereNotNull('owner_type')->count();
        $totalSellers  = Customer::where('owner_type','seller')->count();
        $totalBuilders = Customer::where('owner_type','builder')->count();
        $pendingProps  = DB::table('propertys')->where('post_type',1)->where('request_status','pending')->count();
        $pendingKyc    = DB::table('customer_kyc')->where('status','submitted')->count();
        return view('owner-management.index', compact('totalOwners','totalSellers','totalBuilders','pendingProps','pendingKyc'));
    }

    public function ownerList(Request $request)
    {
        $offset = $request->input('offset',0);
        $limit  = $request->input('limit',10);
        $sort   = $request->input('sort','id');
        $order  = $request->input('order','desc');

        $query = Customer::whereNotNull('owner_type');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use($s){
                $q->where('name','LIKE',"%$s%")->orWhere('email','LIKE',"%$s%")
                  ->orWhere('mobile','LIKE',"%$s%")->orWhere('company_name','LIKE',"%$s%");
            });
        }
        if ($request->filled('owner_type')) $query->where('owner_type',$request->owner_type);
        if ($request->filled('status'))     $query->where('isActive',$request->status);

        $total = $query->count();
        $rows  = $query->orderBy($sort,$order)->offset($offset)->limit($limit)->get();

        $data = $rows->map(function($c){
            $propCount   = DB::table('propertys')->where('added_by',$c->id)->where('post_type',1)->count();
            $pending     = DB::table('propertys')->where('added_by',$c->id)->where('post_type',1)->where('request_status','pending')->count();
            $plan = DB::table('user_packages as up')
                ->join('packages as pk','pk.id','=','up.package_id')
                ->where('up.user_id',$c->id)->whereNull('up.deleted_at')
                ->where(function($q){$q->whereNull('up.end_date')->orWhere('up.end_date','>=',now());})
                ->value('pk.name');
            return [
                'id'           => $c->id,
                'name'         => $c->name,
                'email'        => $c->email,
                'mobile'       => $c->getRawOriginal('mobile'),
                'owner_type'   => $c->owner_type,
                'company_name' => $c->company_name ?? '—',
                'city'         => $c->city ?? '—',
                'total_props'  => $propCount,
                'pending_props'=> $pending,
                'plan'         => $plan ?? 'Free',
                'kyc_status'   => $c->kyc_status ?? 'pending',
                'isActive'     => $c->isActive,
                'created_at'   => $c->created_at ? $c->created_at->format('d M Y') : '—',
            ];
        });

        return response()->json(['total'=>$total,'rows'=>$data]);
    }

    public function show($id)
    {
        $owner = Customer::whereNotNull('owner_type')->findOrFail($id);
        $properties = DB::table('propertys as p')
            ->leftJoin('categories as c','c.id','=','p.category_id')
            ->where('p.added_by',$id)->where('p.post_type',1)
            ->select('p.*','c.category as category_name')
            ->orderBy('p.created_at','desc')->get();

        $propIds = DB::table('propertys')->where('added_by',$id)->where('post_type',1)->pluck('id');
        $enquiries = DB::table('interested_users as iu')
            ->join('customers as c','c.id','=','iu.customer_id')
            ->join('propertys as p','p.id','=','iu.property_id')
            ->whereIn('iu.property_id',$propIds)
            ->select('iu.created_at','c.name as buyer_name','c.email as buyer_email','p.title as property_title','iu.status')
            ->orderBy('iu.created_at','desc')->limit(10)->get();

        $activePlan = DB::table('user_packages as up')
            ->join('packages as pk','pk.id','=','up.package_id')
            ->where('up.user_id',$id)->whereNull('up.deleted_at')
            ->where(function($q){$q->whereNull('up.end_date')->orWhere('up.end_date','>=',now());})
            ->select('pk.name as plan_name','pk.price','up.start_date','up.end_date')->first();

        $packages = DB::table('packages')->where('status',1)->get();
        $stats = [
            'total'     => count($properties),
            'approved'  => collect($properties)->where('request_status','approved')->count(),
            'pending'   => collect($properties)->where('request_status','pending')->count(),
            'rejected'  => collect($properties)->where('request_status','rejected')->count(),
            'views'     => collect($properties)->sum('total_click'),
            'enquiries' => DB::table('interested_users')->whereIn('property_id',$propIds)->count(),
        ];
        return view('owner-management.show', compact('owner','properties','enquiries','activePlan','packages','stats'));
    }

    public function toggleStatus(Request $request)
    {
        Customer::where('id',$request->id)->whereNotNull('owner_type')->update(['isActive'=>$request->status]);
        return response()->json(['error'=>false,'message'=>'Owner '.($request->status?'activated':'suspended').' successfully.']);
    }

    public function assignPlan(Request $request)
    {
        $package = DB::table('packages')->where('id',$request->package_id)->first();
        if (!$package) return response()->json(['error'=>true,'message'=>'Package not found.']);
        DB::table('user_packages')->where('user_id',$request->owner_id)->whereNull('deleted_at')->update(['deleted_at'=>now()]);
        DB::table('user_packages')->insert([
            'user_id'=>$request->owner_id,'package_id'=>$request->package_id,
            'start_date'=>now(),'end_date'=>now()->addHours($package->duration??720),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        return response()->json(['error'=>false,'message'=>$package->name.' plan assigned successfully!']);
    }
}
