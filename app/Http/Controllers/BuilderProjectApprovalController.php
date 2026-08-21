<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuilderProjectApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status=$request->get('status','pending');
        $q=DB::table('projects as p')
            ->join('customers as c','c.id','=','p.added_by')
            ->leftJoin('builder_profiles as bp','bp.customer_id','=','c.id')
            ->leftJoin('builder_project_details as d','d.project_id','=','p.id')
            ->where('c.owner_type','builder')
            ->select('p.*','c.name as owner_name','c.mobile','c.kyc_status as owner_kyc_status','bp.company_name','d.rera_number','d.project_segment','d.admin_remarks');

        if($status!=='all')$q->where('p.request_status',$status);
        if($request->filled('search')){
            $s=$request->search;
            $q->where(function($x)use($s){
                $x->where('p.title','like',"%$s%")->orWhere('p.city','like',"%$s%")->orWhere('bp.company_name','like',"%$s%");
            });
        }
        $rows=$q->orderByDesc('p.id')->paginate(20)->withQueryString();
        $counts=[
            'pending'=>DB::table('projects as p')->join('customers as c','c.id','=','p.added_by')->where('c.owner_type','builder')->where('p.request_status','pending')->count(),
            'approved'=>DB::table('projects as p')->join('customers as c','c.id','=','p.added_by')->where('c.owner_type','builder')->where('p.request_status','approved')->count(),
            'changes_requested'=>DB::table('projects as p')->join('customers as c','c.id','=','p.added_by')->where('c.owner_type','builder')->where('p.request_status','changes_requested')->count(),
            'rejected'=>DB::table('projects as p')->join('customers as c','c.id','=','p.added_by')->where('c.owner_type','builder')->where('p.request_status','rejected')->count(),
        ];
        return view('builder-project-approval.index',compact('rows','counts','status'));
    }

    public function show($id)
    {
        $project=DB::table('projects as p')
            ->join('customers as c','c.id','=','p.added_by')
            ->leftJoin('builder_profiles as bp','bp.customer_id','=','c.id')
            ->leftJoin('builder_project_details as d','d.project_id','=','p.id')
            ->where('p.id',$id)->where('c.owner_type','builder')
            ->select('p.*','c.name as owner_name','c.email as owner_email','c.mobile as owner_mobile','c.kyc_status as owner_kyc_status','bp.company_name','bp.rera_promoter_number',
                'd.project_segment','d.project_subtype','d.launch_date','d.possession_date','d.rera_number','d.rera_url','d.rera_certificate',
                'd.total_land_area','d.land_area_unit','d.total_towers','d.total_blocks','d.total_floors','d.total_units','d.available_units',
                'd.open_space_percent','d.amenities','d.specifications','d.nearby_places','d.admin_remarks')
            ->first();
        if(!$project)abort(404);

        $units=DB::table('builder_project_units')->where('project_id',$id)->get();
        $images=DB::table('builder_project_images')->where('project_id',$id)->orderBy('sort_order')->get();
        $floorPlans=DB::table('builder_project_floor_plans')->where('project_id',$id)->get();
        return view('builder-project-approval.show',compact('project','units','images','floorPlans'));
    }

    public function updateStatus(Request $request,$id)
    {
        $request->validate(['status'=>'required|in:approved,changes_requested,rejected','remarks'=>'nullable|string|max:1500']);
        if(in_array($request->status,['changes_requested','rejected'],true) && !$request->filled('remarks')){
            return back()->with('error','Remarks are required when requesting changes or rejecting a project.');
        }

        $exists=DB::table('projects as p')->join('customers as c','c.id','=','p.added_by')
            ->where('p.id',$id)->where('c.owner_type','builder')->exists();
        if(!$exists)abort(404);

        DB::transaction(function()use($request,$id){
            DB::table('projects')->where('id',$id)->update([
                'request_status'=>$request->status,
                'status'=>$request->status==='approved'?1:0,
                'updated_at'=>now()
            ]);
            DB::table('builder_project_details')->where('project_id',$id)->update([
                'admin_remarks'=>$request->status==='approved'?null:$request->remarks,
                'approved_at'=>$request->status==='approved'?now():null,
                'updated_at'=>now()
            ]);
        });

        $message=$request->status==='approved'
            ? 'Project approved and published successfully.'
            : ($request->status==='changes_requested'?'Changes requested from Builder / Developer.':'Project rejected.');
        return redirect('/builder-project-approvals?status='.$request->status)->with('success',$message);
    }
}
