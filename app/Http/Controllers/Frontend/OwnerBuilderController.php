<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class OwnerBuilderController extends Controller
{
    private function owner(Request $request)
    {
        $s = $request->session()->get('bw_customer', []);
        $id = $s['id'] ?? $s['customer_id'] ?? null;
        if (!$id && !empty($s['email'])) $id = DB::table('customers')->where('email',$s['email'])->value('id');
        if (!$id) return null;

        return DB::table('customers')->where('id',$id)->where('owner_type','builder')->first();
    }

    private function builderOnly(Request $request)
    {
        $owner = $this->owner($request);
        if (!$owner) return [null, redirect('/owner/dashboard')->with('error','This section is available only for Builder / Developer accounts.')];
        return [$owner,null];
    }

    private function profile(int $customerId)
    {
        return DB::table('builder_profiles')->where('customer_id',$customerId)->first();
    }

    private function postingGate($owner)
    {
        if (strtolower((string)($owner->kyc_status ?? 'pending')) !== 'approved') {
            return redirect('/owner/kyc')->with('error','Personal Aadhaar KYC must be approved before posting projects.');
        }
        $p = $this->profile((int)$owner->id);
        if (strtolower((string)($p->status ?? 'draft')) !== 'approved') {
            return redirect('/owner/builder-verification')->with('error','Company verification must be approved before posting projects.');
        }
        return null;
    }

    private function categories()
    {
        return DB::table('categories')->where('status',1)->orderBy('category')->get();
    }

    private function projectForOwner($id, $owner)
    {
        return DB::table('projects')->where('id',$id)->where('added_by',$owner->id)->first();
    }

    private function uploadProjectFiles(Request $request, int $projectId, bool $replaceCover = false): void
    {
        if ($request->hasFile('cover_image')) {
            $relativeDir = trim((string)config('global.PROJECT_IMG_PATH','project/'),'/');
            if ($relativeDir === '') $relativeDir='project';
            $imageDir = public_path('images/'.$relativeDir);
            if (!File::isDirectory($imageDir)) File::makeDirectory($imageDir,0755,true);

            $f=$request->file('cover_image');
            $name=time().'_'.Str::lower(Str::random(5)).'.'.$f->getClientOriginalExtension();
            $f->move($imageDir,$name);
            DB::table('projects')->where('id',$projectId)->update(['image'=>$name,'updated_at'=>now()]);
        }

        $dir=public_path('images/builder_projects/'.$projectId);
        if (!File::isDirectory($dir)) File::makeDirectory($dir,0755,true);

        $sort=(int)(DB::table('builder_project_images')->where('project_id',$projectId)->max('sort_order') ?? 0);
        foreach ($request->file('gallery',[]) as $i=>$f) {
            $name='gallery_'.time().'_'.$i.'.'.$f->getClientOriginalExtension();
            $f->move($dir,$name);
            DB::table('builder_project_images')->insert([
                'project_id'=>$projectId,'image'=>$name,'image_type'=>'gallery','sort_order'=>$sort+$i+1,
                'created_at'=>now(),'updated_at'=>now()
            ]);
        }

        $titles=$request->input('floor_plan_title',[]);
        foreach ($request->file('floor_plan_file',[]) as $i=>$f) {
            if (!$f) continue;
            $name='floor_'.time().'_'.$i.'.'.$f->getClientOriginalExtension();
            $f->move($dir,$name);
            DB::table('builder_project_floor_plans')->insert([
                'project_id'=>$projectId,
                'title'=>trim((string)($titles[$i] ?? 'Floor Plan')) ?: 'Floor Plan',
                'file_name'=>$name,'created_at'=>now(),'updated_at'=>now()
            ]);
        }
    }

    private function saveExtended(Request $request, int $projectId, bool $update=false): void
    {
        $data=[
            'project_segment'=>$request->project_segment,
            'project_subtype'=>$request->project_subtype,
            'launch_date'=>$request->launch_date ?: null,
            'possession_date'=>$request->possession_date ?: null,
            'rera_number'=>$request->rera_number,
            'rera_url'=>$request->rera_url,
            'total_land_area'=>$request->total_land_area ?: null,
            'land_area_unit'=>$request->land_area_unit ?: 'sqft',
            'total_towers'=>$request->total_towers ?: null,
            'total_blocks'=>$request->total_blocks ?: null,
            'total_floors'=>$request->total_floors ?: null,
            'total_units'=>$request->total_units ?: null,
            'available_units'=>$request->available_units ?: null,
            'open_space_percent'=>$request->open_space_percent ?: null,
            'amenities'=>json_encode(array_values(array_filter($request->input('amenities',[])))),
            'specifications'=>json_encode(array_filter($request->input('specifications',[]))),
            'nearby_places'=>json_encode(array_filter($request->input('nearby_places',[]))),
            'admin_remarks'=>null,
            'submitted_at'=>now(),
            'approved_at'=>null,
            'updated_at'=>now(),
        ];

        if ($request->hasFile('rera_certificate')) {
            $dir=public_path('images/builder_projects/'.$projectId);
            if (!File::isDirectory($dir)) File::makeDirectory($dir,0755,true);
            $f=$request->file('rera_certificate');
            $name='rera_'.time().'.'.$f->getClientOriginalExtension();
            $f->move($dir,$name);
            $data['rera_certificate']=$name;
        }

        if ($update) {
            DB::table('builder_project_details')->where('project_id',$projectId)->update($data);
        } else {
            $data['project_id']=$projectId;
            $data['created_at']=now();
            DB::table('builder_project_details')->insert($data);
        }

        DB::table('builder_project_units')->where('project_id',$projectId)->delete();
        foreach ($request->input('unit_configuration',[]) as $i=>$configuration) {
            if (!trim((string)$configuration)) continue;
            DB::table('builder_project_units')->insert([
                'project_id'=>$projectId,
                'configuration'=>$configuration,
                'carpet_area'=>$request->input("unit_carpet_area.$i") ?: null,
                'built_up_area'=>$request->input("unit_built_up_area.$i") ?: null,
                'starting_price'=>$request->input("unit_starting_price.$i") ?: null,
                'maximum_price'=>$request->input("unit_maximum_price.$i") ?: null,
                'available_units'=>$request->input("unit_available.$i") ?: null,
                'created_at'=>now(),'updated_at'=>now()
            ]);
        }
    }

    public function verification(Request $request)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        $profile=$this->profile((int)$owner->id);
        return view('frontend.owner.builder.verification',compact('owner','profile'));
    }

    public function submitVerification(Request $request)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        $profile=$this->profile((int)$owner->id);
        $currentStatus=strtolower((string)($profile->status ?? 'draft'));
        if(in_array($currentStatus,['submitted','under_review'],true)) return back()->with('error','Company verification is under review and cannot be edited.');
        if($currentStatus==='approved') return back()->with('error','Company verification is approved. Contact admin to change verified details.');

        $request->validate([
            'company_name'=>'required|string|max:180','company_type'=>'required|string|max:60',
            'contact_person'=>'required|string|max:150','pan_number'=>'required|string|max:20',
            'registered_office_address'=>'required|string|max:1000','city'=>'required|string|max:100','state'=>'required|string|max:100',
            'pan_document'=>[$profile && $profile->pan_document?'nullable':'required','file','mimes:jpg,jpeg,png,pdf','max:5120'],
            'registration_certificate'=>[$profile && $profile->registration_certificate?'nullable':'required','file','mimes:jpg,jpeg,png,pdf','max:5120'],
            'logo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gst_certificate'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'rera_certificate'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'authorised_person_aadhaar'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $folder=public_path('images/builder_kyc/'.$owner->id);
        if(!File::isDirectory($folder))File::makeDirectory($folder,0755,true);
        $fields=['logo','pan_document','gst_certificate','registration_certificate','rera_certificate','authorised_person_aadhaar'];
        $saved=[];
        foreach($fields as $field){
            $saved[$field]=$profile->{$field}??null;
            if($request->hasFile($field)){
                $f=$request->file($field);
                $n=$field.'_'.time().'_'.Str::lower(Str::random(4)).'.'.$f->getClientOriginalExtension();
                $f->move($folder,$n);$saved[$field]=$n;
            }
        }

        $data=[
            'customer_id'=>$owner->id,'company_name'=>$request->company_name,'company_type'=>$request->company_type,
            'contact_person'=>$request->contact_person,'pan_number'=>strtoupper($request->pan_number),
            'gst_number'=>$request->gst_number?strtoupper($request->gst_number):null,
            'cin_llpin'=>$request->cin_llpin?strtoupper($request->cin_llpin):null,
            'rera_promoter_number'=>$request->rera_promoter_number,
            'registered_office_address'=>$request->registered_office_address,'city'=>$request->city,'state'=>$request->state,
            'website'=>$request->website,'years_in_business'=>$request->years_in_business,'about_developer'=>$request->about_developer,
            'logo'=>$saved['logo'],'pan_document'=>$saved['pan_document'],'gst_certificate'=>$saved['gst_certificate'],
            'registration_certificate'=>$saved['registration_certificate'],'rera_certificate'=>$saved['rera_certificate'],
            'authorised_person_aadhaar'=>$saved['authorised_person_aadhaar'],
            'status'=>'submitted','admin_remarks'=>null,'submitted_at'=>now(),'updated_at'=>now()
        ];
        if($profile) DB::table('builder_profiles')->where('id',$profile->id)->update($data);
        else{$data['created_at']=now();DB::table('builder_profiles')->insert($data);}
        DB::table('customers')->where('id',$owner->id)->update(['company_name'=>$request->company_name,'updated_at'=>now()]);
        return redirect('/owner/builder-verification')->with('success','Builder / Developer verification submitted for admin approval.');
    }

    public function myProjects(Request $request)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        $projects=DB::table('projects as p')
            ->leftJoin('builder_project_details as d','d.project_id','=','p.id')
            ->where('p.added_by',$owner->id)
            ->select('p.*','d.project_segment','d.project_subtype','d.rera_number','d.total_units','d.available_units','d.admin_remarks')
            ->orderByDesc('p.id')->paginate(12);

        foreach($projects as $p){
            $p->enquiry_count=DB::table('project_enquiries')->where('project_id',$p->id)->count();
            $p->new_enquiry_count=DB::table('project_enquiries')->where('project_id',$p->id)->where('status','new')->count();
        }

        $stats=[
            'total'=>DB::table('projects')->where('added_by',$owner->id)->count(),
            'live'=>DB::table('projects')->where('added_by',$owner->id)->where('request_status','approved')->where('status',1)->count(),
            'pending'=>DB::table('projects')->where('added_by',$owner->id)->where('request_status','pending')->count(),
            'changes'=>DB::table('projects')->where('added_by',$owner->id)->where('request_status','changes_requested')->count(),
            'enquiries'=>DB::table('project_enquiries as e')->join('projects as p','p.id','=','e.project_id')->where('p.added_by',$owner->id)->count(),
        ];
        $profile=$this->profile((int)$owner->id);
        return view('frontend.owner.builder.projects',compact('owner','projects','profile','stats'));
    }

    public function createProject(Request $request)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        if($r=$this->postingGate($owner))return $r;
        $categories=$this->categories();$profile=$this->profile((int)$owner->id);
        $project=null;$details=null;$units=collect();$images=collect();$floorPlans=collect();
        return view('frontend.owner.builder.post-project',compact('owner','categories','profile','project','details','units','images','floorPlans'));
    }

    public function storeProject(Request $request)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        if($r=$this->postingGate($owner))return $r;
        $this->validateProject($request,true);

        $slug=Str::slug($request->title).'-'.Str::lower(Str::random(6));
        $reference='BWP'.date('Y').strtoupper(Str::random(5));

        $projectId=DB::table('projects')->insertGetId([
            'reference_no'=>$reference,'title'=>$request->title,'slug_id'=>$slug,'description'=>$request->description,
            'meta_title'=>$request->meta_title,'meta_description'=>$request->meta_description,'meta_keywords'=>$request->meta_keywords,
            'image'=>'','video_link'=>$request->video_link,'location'=>$request->location,'latitude'=>$request->latitude?:'0',
            'longitude'=>$request->longitude?:'0','city'=>$request->city,'state'=>$request->state,'country'=>$request->country?:'India',
            'type'=>$request->project_status,'added_by'=>$owner->id,'category_id'=>$request->category_id,
            'status'=>0,'request_status'=>'pending','total_click'=>0,'is_admin_listing'=>0,'created_at'=>now(),'updated_at'=>now()
        ]);
        $this->uploadProjectFiles($request,$projectId);
        $this->saveExtended($request,$projectId,false);

        return redirect('/owner/my-projects')->with('success','Project submitted successfully and is waiting for admin approval.');
    }

    public function editProject(Request $request,$id)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        if($r=$this->postingGate($owner))return $r;
        $project=$this->projectForOwner($id,$owner); if(!$project)abort(404);

        if(in_array($project->request_status,['pending','approved'],true)){
            return redirect('/owner/my-projects')->with('error','This project cannot be edited while it is under review or live.');
        }

        $categories=$this->categories();$profile=$this->profile((int)$owner->id);
        $details=DB::table('builder_project_details')->where('project_id',$id)->first();
        $units=DB::table('builder_project_units')->where('project_id',$id)->get();
        $images=DB::table('builder_project_images')->where('project_id',$id)->orderBy('sort_order')->get();
        $floorPlans=DB::table('builder_project_floor_plans')->where('project_id',$id)->get();

        return view('frontend.owner.builder.post-project',compact('owner','categories','profile','project','details','units','images','floorPlans'));
    }

    public function updateProject(Request $request,$id)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        if($r=$this->postingGate($owner))return $r;
        $project=$this->projectForOwner($id,$owner); if(!$project)abort(404);
        if(in_array($project->request_status,['pending','approved'],true))return redirect('/owner/my-projects')->with('error','This project cannot be edited while it is under review or live.');

        $this->validateProject($request,false);

        DB::table('projects')->where('id',$id)->update([
            'title'=>$request->title,'description'=>$request->description,'category_id'=>$request->category_id,
            'video_link'=>$request->video_link,'location'=>$request->location,'latitude'=>$request->latitude?:'0',
            'longitude'=>$request->longitude?:'0','city'=>$request->city,'state'=>$request->state,'country'=>$request->country?:'India',
            'type'=>$request->project_status,'status'=>0,'request_status'=>'pending',
            'meta_title'=>$request->meta_title,'meta_description'=>$request->meta_description,'meta_keywords'=>$request->meta_keywords,
            'updated_at'=>now()
        ]);
        $this->uploadProjectFiles($request,(int)$id);
        $this->saveExtended($request,(int)$id,true);

        return redirect('/owner/my-projects')->with('success','Project changes submitted for admin review.');
    }

    public function enquiries(Request $request)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        $rows=DB::table('project_enquiries as e')->join('projects as p','p.id','=','e.project_id')
            ->where('p.added_by',$owner->id)
            ->select('e.*','p.title as project_title','p.reference_no')
            ->orderByDesc('e.id')->paginate(20);
        return view('frontend.owner.builder.enquiries',compact('owner','rows'));
    }

    public function updateEnquiryStatus(Request $request,$id)
    {
        [$owner,$redirect]=$this->builderOnly($request); if($redirect)return $redirect;
        $request->validate(['status'=>'required|in:new,contacted,closed']);
        $valid=DB::table('project_enquiries as e')->join('projects as p','p.id','=','e.project_id')
            ->where('e.id',$id)->where('p.added_by',$owner->id)->exists();
        if(!$valid)abort(404);
        DB::table('project_enquiries')->where('id',$id)->update(['status'=>$request->status,'updated_at'=>now()]);
        return back()->with('success','Enquiry status updated.');
    }

    private function validateProject(Request $request,bool $coverRequired): void
    {
        $request->validate([
            'title'=>'required|string|max:191','category_id'=>'required|integer',
            'description'=>'required|string|max:10000','project_segment'=>'required|in:residential,commercial,mixed,plotted',
            'project_subtype'=>'required|string|max:80','project_status'=>'required|in:Upcoming,New Launch,Under Construction,Ready to Move',
            'location'=>'required|string|max:191','city'=>'required|string|max:191','state'=>'required|string|max:191',
            'cover_image'=>[$coverRequired?'required':'nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'gallery.*'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'floor_plan_file.*'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'rera_certificate'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
    }
}