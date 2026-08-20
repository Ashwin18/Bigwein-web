<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectMarketplaceController extends Controller
{
    private function base()
    {
        return DB::table('projects as p')
            ->leftJoin('categories as cat','cat.id','=','p.category_id')
            ->leftJoin('customers as c','c.id','=','p.added_by')
            ->leftJoin('builder_profiles as bp','bp.customer_id','=','c.id')
            ->leftJoin('builder_project_details as d','d.project_id','=','p.id')
            ->where('p.status',1)
            ->where('p.request_status','approved');
    }

    public function index(Request $request)
    {
        $q=$this->base();
        if($request->filled('city'))$q->where('p.city','like','%'.$request->city.'%');
        if($request->filled('type'))$q->where('p.type',$request->type);
        if($request->filled('segment'))$q->where('d.project_segment',$request->segment);

        $projects=$q->select('p.*','cat.category as category_name','c.name as owner_name','bp.company_name',
            'd.project_segment','d.project_subtype','d.possession_date','d.rera_number','d.total_units','d.available_units')
            ->orderByDesc('p.id')->paginate(12)->withQueryString();

        foreach($projects as $p){
            $prices=DB::table('builder_project_units')->where('project_id',$p->id)
                ->selectRaw('MIN(starting_price) as min_price, MAX(maximum_price) as max_price')->first();
            $p->min_price=$prices->min_price??null;
            $p->max_price=$prices->max_price??null;
            $p->configurations=DB::table('builder_project_units')->where('project_id',$p->id)->pluck('configuration')->implode(', ');
        }
        $cities=DB::table('projects')->where('status',1)->where('request_status','approved')->whereNotNull('city')->distinct()->orderBy('city')->pluck('city');
        return view('frontend.projects.index',compact('projects','cities'));
    }

    public function show($slug)
    {
        $project=$this->base()->where('p.slug_id',$slug)
            ->select('p.*','cat.category as category_name','c.name as owner_name','bp.company_name','bp.logo as builder_logo',
                'bp.about_developer','bp.years_in_business','bp.rera_promoter_number',
                'd.project_segment','d.project_subtype','d.launch_date','d.possession_date','d.rera_number','d.rera_url',
                'd.total_land_area','d.land_area_unit','d.total_towers','d.total_blocks','d.total_floors','d.total_units','d.available_units',
                'd.open_space_percent','d.amenities','d.specifications','d.nearby_places')
            ->first();
        if(!$project)abort(404);

        DB::table('projects')->where('id',$project->id)->increment('total_click');
        $project->total_click=((int)$project->total_click)+1;

        $units=DB::table('builder_project_units')->where('project_id',$project->id)->get();
        $images=DB::table('builder_project_images')->where('project_id',$project->id)->orderBy('sort_order')->get();
        $floorPlans=DB::table('builder_project_floor_plans')->where('project_id',$project->id)->get();
        $properties=DB::table('propertys')->where('project_id',$project->id)->where('request_status','approved')->where('status',1)
            ->select('id','title','slug_id','price','city','state','title_image','sub_type','total_area','carpet_area','tower','unit_number')
            ->orderByDesc('id')->limit(6)->get();

        return view('frontend.projects.show',compact('project','units','images','floorPlans','properties'));
    }

    public function enquiry(Request $request)
    {
        $d=$request->validate([
            'project_id'=>'required|integer|exists:projects,id','name'=>'required|string|max:120',
            'mobile'=>'required|string|max:30','email'=>'nullable|email|max:160',
            'configuration'=>'nullable|string|max:80','budget'=>'nullable|string|max:80','message'=>'nullable|string|max:1000'
        ]);
        $live=DB::table('projects')->where('id',$d['project_id'])->where('status',1)->where('request_status','approved')->exists();
        if(!$live)return back()->with('error','This project is not currently available for enquiry.');
        $d['customer_id']=session('bw_customer.id')??null;$d['status']='new';$d['created_at']=now();$d['updated_at']=now();
        DB::table('project_enquiries')->insert($d);
        return back()->with('success','Your project enquiry has been sent to the Builder / Developer.');
    }
}