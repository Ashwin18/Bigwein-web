<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OwnerProjectController extends Controller
{
    public function create()
    {
        if (!session("bw_customer")) return redirect("/owner/login");
        $s = \App\Http\Controllers\Frontend\FrontendController::settings(["web_logo","company_name","currency_symbol"]);
        $customer = session("bw_customer");
        try { $categories = \App\Models\Category::where("status",1)->get(); } catch(\Exception $e) { $categories = collect(); }
        return view("frontend.owner.post-project", compact("s","customer","categories"));
    }

    public function store(Request $request)
    {
        if (!session("bw_customer")) {
            return response()->json(["success"=>false,"message"=>"Please login first"]);
        }
        $customer = session("bw_customer");

        $request->validate([
            "title"       => "required|string|max:200",
            "description" => "required|string",
            "city"        => "required|string|max:100",
        ]);

        $slug = Str::slug($request->title)."-".substr(uniqid(),0,6);

        // Generate reference number
        do {
            $ref = "BWP".date("Y").strtoupper(substr(bin2hex(random_bytes(3)),0,5));
        } while (DB::table("projects")->where("reference_no",$ref)->exists());

        $imgName = null;
        if ($request->hasFile("image")) {
            $file    = $request->file("image");
            $imgName = time()."_".preg_replace("/[^a-zA-Z0-9.]/","_",$file->getClientOriginalName());
            $dest    = public_path("images/projects");
            if (!is_dir($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $imgName);
        }

        DB::table("projects")->insert([
            "title"          => $request->title,
            "slug_id"        => $slug,
            "category_id"    => $request->category_id ?? 1,
            "description"    => $request->description,
            "location"       => $request->address ?? $request->city,
            "city"           => $request->city,
            "state"          => $request->state ?? "",
            "country"        => "India",
            "type"           => $request->project_status ?? "New Launch",
            "image"          => $imgName,
            "latitude"       => $request->latitude ?? 0,
            "longitude"      => $request->longitude ?? 0,
            "added_by"       => $customer["id"],
            "is_admin_listing"=> 0,
            "status"         => 1,
            "request_status" => "pending",
            "reference_no"   => $ref,
            "total_click"    => 0,
            "meta_keywords"  => "",
            "created_at"     => now(),
            "updated_at"     => now(),
        ]);

        return response()->json([
            "success" => true,
            "message" => "Project submitted successfully! It will be reviewed and published within 24 hours."
        ]);
    }
}
