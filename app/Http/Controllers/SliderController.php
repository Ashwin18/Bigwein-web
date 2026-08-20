<?php
namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy("sequence")->get();
        return view("slider.index", compact("sliders"));
    }

    public function create()
    {
        return view("slider.create");
    }

    public function store(Request $request)
    {
        $request->validate(["web_image" => "required|image|mimes:jpeg,png,jpg,gif,webp|max:3072"]);

        $slider = new Slider();

        // Web/Desktop image (main banner)
        if ($request->hasFile("web_image")) {
            $file = $request->file("web_image");
            $name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/","_",$file->getClientOriginalName());
            $dest = public_path("images/slider");
            if (!is_dir($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $name);
            $slider->web_image = $name;
            $slider->image     = $name; // same for mobile fallback
        }

        $slider->type     = $request->type ?? "Banner";
        $slider->link     = $request->link ?? null;
        $slider->sequence = $request->sequence ?? 1;
        $slider->show_property_details = 0;
        $slider->default_data = 0;
        $slider->save();

        Cache::forget("bw_sliders");
        return redirect()->route("slider.index")->with("success", "Banner uploaded successfully!");
    }

    public function show($id)
    {
        return redirect()->route("slider.index");
    }

    public function edit($id)
    {
        $slider = Slider::findOrFail($id);
        return view("slider.edit", compact("slider"));
    }

    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);

        if ($request->hasFile("web_image")) {
            $request->validate(["web_image" => "image|mimes:jpeg,png,jpg,gif,webp|max:3072"]);
            $file = $request->file("web_image");
            $name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/","_",$file->getClientOriginalName());
            $dest = public_path("images/slider");
            if (!is_dir($dest)) mkdir($dest, 0755, true);
            foreach([$slider->web_image, $slider->image] as $old) {
                if ($old && file_exists(public_path("images/slider/".$old))) @unlink(public_path("images/slider/".$old));
            }
            $file->move($dest, $name);
            $slider->web_image = $name;
            $slider->image     = $name;
        }

        $slider->type     = $request->type ?? $slider->type;
        $slider->link     = $request->link ?? $slider->link;
        $slider->sequence = $request->sequence ?? $slider->sequence;
        $slider->save();

        Cache::forget("bw_sliders");
        return redirect()->route("slider.index")->with("success", "Banner updated!");
    }

    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        foreach([$slider->web_image, $slider->image] as $img) {
            if ($img && file_exists(public_path("images/slider/".$img))) @unlink(public_path("images/slider/".$img));
        }
        $slider->delete();
        Cache::forget("bw_sliders");
        return redirect()->route("slider.index")->with("success", "Banner deleted!");
    }

    public function sliderList()
    {
        $sliders = Slider::orderBy("sequence")->get();
        return response()->json(["error" => false, "data" => $sliders]);
    }
}
