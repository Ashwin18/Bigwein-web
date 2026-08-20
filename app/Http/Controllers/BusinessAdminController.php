<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class BusinessAdminController extends Controller {
 public function index(Request $r){
  $status=$r->get('status','pending');$q=DB::table('businesses as b')->leftJoin('customers as cu','cu.id','=','b.customer_id')->leftJoin('business_categories as c','c.id','=','b.business_category_id');
  if($status!=='all')$q->where('b.request_status',$status);if($r->filled('search')){$s=$r->search;$q->where(function($x)use($s){$x->where('b.title','like',"%$s%")->orWhere('b.city','like',"%$s%")->orWhere('cu.name','like',"%$s%");});}
  $rows=$q->select('b.*','cu.name as owner_name','cu.mobile as owner_mobile','c.name as category_name')->orderByDesc('b.id')->paginate(15)->withQueryString();
  $counts=['pending'=>DB::table('businesses')->where('request_status','pending')->count(),'approved'=>DB::table('businesses')->where('request_status','approved')->count(),'changes_requested'=>DB::table('businesses')->where('request_status','changes_requested')->count(),'rejected'=>DB::table('businesses')->where('request_status','rejected')->count(),'draft'=>DB::table('businesses')->where('request_status','draft')->count()];
  return view('business-management.index',compact('rows','counts','status'));
 }
 public function show($id){
  $business=DB::table('businesses as b')->leftJoin('customers as cu','cu.id','=','b.customer_id')->leftJoin('business_categories as c','c.id','=','b.business_category_id')->where('b.id',$id)->select('b.*','cu.name as owner_name','cu.mobile as owner_mobile','cu.email as owner_email','c.name as category_name')->first();if(!$business)abort(404);
  $images=DB::table('business_images')->where('business_id',$id)->get();$documents=DB::table('business_documents')->where('business_id',$id)->get();
  return view('business-management.show',compact('business','images','documents'));
 }
 public function updateStatus(Request $r,$id){
   $r->validate(['status'=>'required|in:approved,rejected,changes_requested','remarks'=>'nullable|max:1000']);
   if(in_array($r->status,['rejected','changes_requested'],true) && !$r->filled('remarks')){
      return back()->with('error','Please enter remarks before rejecting or requesting changes.');
   }
   DB::table('businesses')->where('id',$id)->update([
      'request_status'=>$r->status,
      'status'=>$r->status==='approved'?1:0,
      'admin_remarks'=>$r->remarks,
      'approved_at'=>$r->status==='approved'?now():null,
      'updated_at'=>now()
   ]);
   $msg=$r->status==='changes_requested'?'Changes requested from seller.':'Business '.$r->status.' successfully.';
   return back()->with('success',$msg);
 }
 public function enquiries(Request $r){
   $status=$r->get('status','all');
   $q=DB::table('business_enquiries as e')
      ->join('businesses as b','b.id','=','e.business_id')
      ->leftJoin('customers as cu','cu.id','=','b.customer_id');
   if($status!=='all') $q->where('e.status',$status);
   if($r->filled('search')){
      $s=$r->search;
      $q->where(function($x)use($s){
         $x->where('e.name','like',"%$s%")
           ->orWhere('e.mobile','like',"%$s%")
           ->orWhere('e.email','like',"%$s%")
           ->orWhere('b.title','like',"%$s%")
           ->orWhere('cu.name','like',"%$s%");
      });
   }
   $rows=$q->select('e.*','b.title as business_title','b.reference_no','b.city','b.state','cu.name as seller_name')
      ->orderByDesc('e.id')->paginate(20)->withQueryString();
   $counts=[
      'new'=>DB::table('business_enquiries')->where('status','new')->count(),
      'contacted'=>DB::table('business_enquiries')->where('status','contacted')->count(),
      'closed'=>DB::table('business_enquiries')->where('status','closed')->count(),
      'all'=>DB::table('business_enquiries')->count(),
   ];
   return view('business-management.enquiries',compact('rows','counts','status'));
 }

 public function updateEnquiryStatus(Request $r,$id){
   $r->validate(['status'=>'required|in:new,contacted,closed']);
   DB::table('business_enquiries')->where('id',$id)->update(['status'=>$r->status,'updated_at'=>now()]);
   return back()->with('success','Business enquiry status updated.');
 }

 public function categories(){ $rows=DB::table('business_categories')->orderBy('sort_order')->orderBy('name')->get();return view('business-management.categories',compact('rows')); }
 public function saveCategory(Request $r){$r->validate(['name'=>'required|max:120']);$slug=Str::slug($r->name);DB::table('business_categories')->insert(['name'=>$r->name,'slug'=>$slug.'-'.Str::lower(Str::random(3)),'status'=>1,'sort_order'=>$r->sort_order??0,'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Business category saved.');}
}