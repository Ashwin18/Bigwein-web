<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AssignParameters;
use App\Models\OutdoorFacility;
use App\Models\Property;
use App\Models\PropertyImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OwnerPropertyController extends Controller
{
    private function customer()
    {
        return session('bw_customer');
    }


    private function categoryProfileKey($categoryId): string
    {
        if (!is_scalar($categoryId)) return 'generic';

        $name = strtolower((string) DB::table('categories')->where('id', $categoryId)->value('category'));

        if (str_contains($name, 'commercial') || str_contains($name, 'office') || str_contains($name, 'shop') || str_contains($name, 'showroom') || str_contains($name, 'warehouse') || str_contains($name, 'industrial') || str_contains($name, 'factory')) return 'commercial';
        if (str_contains($name, 'plot') || str_contains($name, 'land') || str_contains($name, 'agricultural')) return 'plot';
        if (str_contains($name, 'pg') || str_contains($name, 'hostel') || str_contains($name, 'co-living')) return 'pg';
        if (str_contains($name, 'villa')) return 'villa';
        if (str_contains($name, 'town')) return 'townhouse';
        if (str_contains($name, 'apartment') || str_contains($name, 'flat')) return 'apartment';
        if (str_contains($name, 'house') || str_contains($name, 'bungalow') || str_contains($name, 'banglow') || str_contains($name, 'penthouse') || str_contains($name, 'condo')) return 'residential';

        return 'generic';
    }

    private function categoryValidationRules(string $profile, $parameters): array
    {
        $rules = [
            'sub_type'        => $profile === 'commercial' ? 'required|string|max:100' : 'nullable|string|max:100',
            'commercial_type' => 'nullable|string|max:100',
        ];

        if ($profile !== 'plot') {
            $rules += [
                'carpet_area'     => 'nullable|numeric|min:0',
                'floor_number'    => 'nullable|integer|min:0',
                'total_floors'    => 'nullable|integer|min:0',
                'age_of_building' => 'nullable|string|max:100',
                'furnishing'      => 'nullable|string|max:100',
            ];
        }

        if (in_array($profile, ['villa', 'townhouse', 'apartment', 'residential'], true)) {
            foreach ($parameters as $parameter) {
                $name = preg_replace('/[^a-z0-9]+/', '', strtolower((string) $parameter->name));
                if (str_contains($name, 'bedroom') || str_contains($name, 'bathroom') || str_contains($name, 'bhk')) {
                    $rules['par_' . $parameter->id] = 'nullable|integer|min:0|max:100';
                }
            }
        }

        return $rules;
    }

    private function categoryAllowsParameter(string $profile, $parameter): bool
    {
        $name = preg_replace('/[^a-z0-9]+/', '', strtolower((string) $parameter->name));

        if ($profile === 'plot') {
            foreach (['bedroom', 'bathroom', 'bhk', 'balcon', 'kitchen', 'floor', 'furnish', 'ageofbuilding'] as $blocked) {
                if (str_contains($name, $blocked)) return false;
            }
        }

        if ($profile === 'commercial') {
            foreach (['bedroom', 'bhk', 'balcon'] as $blocked) {
                if (str_contains($name, $blocked)) return false;
            }
        }

        if ($profile === 'pg' && str_contains($name, 'bhk')) return false;

        return true;
    }

    private function sanitizedCategoryData(Request $request): array
    {
        $profile = $this->categoryProfileKey($request->category_id);

        $data = [
            'sub_type'        => $request->sub_type,
            'commercial_type' => $profile === 'commercial' ? $request->sub_type : null,
            'carpet_area'     => $request->carpet_area,
            'floor_number'    => $request->floor_number,
            'total_floors'    => $request->total_floors,
            'age_of_building' => $request->age_of_building,
            'furnishing'      => $request->furnishing,
            'water_supply'    => $request->water_supply,
        ];

        if ($profile === 'plot') {
            $data['carpet_area'] = null;
            $data['floor_number'] = null;
            $data['total_floors'] = null;
            $data['age_of_building'] = null;
            $data['furnishing'] = null;
            $data['water_supply'] = null;
        }

        return [$profile, $data];
    }

    private function builderProjectData($cust): array
    {
        $projects = collect();
        $units = collect();

        if (strtolower((string)($cust['owner_type'] ?? '')) === 'builder' && !empty($cust['id'])) {
            $projects = DB::table('projects as p')
                ->leftJoin('builder_project_details as d','d.project_id','=','p.id')
                ->where('p.added_by',$cust['id'])
                ->where('p.request_status','approved')
                ->where('p.status',1)
                ->select('p.id','p.title','p.category_id','p.location','p.city','p.state','p.country','p.latitude','p.longitude','d.rera_number','d.amenities')
                ->orderBy('p.title')->get();

            if ($projects->count()) {
                $units = DB::table('builder_project_units')->whereIn('project_id',$projects->pluck('id'))->orderBy('configuration')->get();
            }
        }

        return [$projects,$units];
    }

    /** Show create form */
    public function create(Request $request)
    {
        $cust       = $this->customer();
        if (empty($cust['id'])) {
            return redirect('/owner/login')->with('error', 'Please login to continue.');
        }
        $categories = DB::table('categories')->where('status', 1)->get();
        $parameters = DB::table('parameters')->get();
        $facilities = DB::table('outdoor_facilities')->get();
        $propertyGroup = $request->query('property_group', '');
        $listingMode = $request->query('listing_mode', '');
        [$builderProjects,$builderProjectUnits] = $this->builderProjectData($cust);
        return view('frontend.owner.post-property', compact('cust', 'categories', 'parameters', 'facilities', 'propertyGroup', 'listingMode', 'builderProjects', 'builderProjectUnits'));
    }

    /** Store new property */
    public function store(Request $request)
    {
        $cust   = $this->customer();
        $custId = $cust['id'] ?? null;
        if (!$custId) {
            return redirect('/owner/login')->with('error', 'Please login to continue.');
        }

        $categoryProfile = $this->categoryProfileKey($request->category_id);
        $parameters = DB::table('parameters')->get();

        $request->validate(array_merge([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'category_id' => 'required|integer',
            'propery_type'=> 'required|in:0,1',
            'address'     => 'required|string',
            'city'        => 'required|string',
            'state'       => 'required|string',
            'price'       => 'required|numeric|min:1',
            'title_image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'project_id'  => 'nullable|integer',
            'project_unit_id' => 'nullable|integer',
            'tower'       => 'nullable|string|max:80',
            'unit_number' => 'nullable|string|max:80',
        ], $this->categoryValidationRules($categoryProfile, $parameters)));

        $slug = Str::slug($request->title) . '-' . Str::random(6);

        [$categoryProfile, $categoryData] = $this->sanitizedCategoryData($request);

        $propData = [
            'title'           => $request->title,
            'slug_id'         => $slug,
            'description'     => $request->description,
            'category_id'     => $request->category_id,
            'propery_type'    => $request->propery_type,
            'sub_type'        => $categoryData['sub_type'],
            'commercial_type' => $categoryData['commercial_type'],
            'listing_type'    => $request->listing_mode === 'business' ? 'business' : 'property',
            'business_type'   => $request->listing_mode === 'business' ? $request->commercial_type : null,
            'address'         => $request->address,
            'client_address'  => $request->address,
            'city'            => $request->city,
            'state'           => $request->state,
            'country'         => $request->country ?? 'India',
            'pincode'         => $request->pincode,
            'latitude'        => (string) ($request->latitude ?? ''),
            'longitude'       => (string) ($request->longitude ?? ''),
            'price'           => $request->price,
            'total_area'      => $request->total_area,
            'carpet_area'     => $categoryData['carpet_area'],
            'floor_number'    => $categoryData['floor_number'],
            'total_floors'    => $categoryData['total_floors'],
            'age_of_building' => $categoryData['age_of_building'],
            'facing'          => $request->facing,
            'furnishing'      => $categoryData['furnishing'],
            'water_supply'    => $categoryData['water_supply'],
            'prop_status'     => $request->prop_status,
            'maintenance'     => $request->maintenance,
            'security_deposit'=> $request->security_deposit,
            'price_negotiable'=> $request->price_negotiable ? 1 : 0,
            'rentduration'    => $request->rentduration,
            'video_link'      => $request->video_link ?? '',
            'added_by'        => $custId,
            'post_type'       => 1,
            'request_status'  => 'pending',
            'status'          => 0,
            'total_click'     => 0,
            'is_premium'      => 0,
            'business_meta'   => json_encode(array_filter([
                'property_group' => $categoryProfile,
                'source_property_group' => $request->property_group,
                'listing_mode' => $request->listing_mode,
                'land_type' => $request->land_type,
                'area_unit' => $request->area_unit,
                'road_width' => $request->road_width,
                'boundary_wall' => $request->boundary_wall,
            ])),
        ];

        // Main image
        if ($request->hasFile('title_image')) {
            $propData['title_image'] = store_image($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
        }

        $prop = Property::create($propData);

        // Builder-only optional link to an approved project.
        if ($request->filled('project_id')) {
            $ownsProject = DB::table('projects')
                ->where('id',$request->project_id)
                ->where('added_by',$custId)
                ->where('request_status','approved')
                ->where('status',1)
                ->exists();

            if ($ownsProject) {
                $unitId = null;
                if ($request->filled('project_unit_id')) {
                    $unitId = DB::table('builder_project_units')
                        ->where('id',$request->project_unit_id)
                        ->where('project_id',$request->project_id)
                        ->value('id');
                }
                DB::table('propertys')->where('id',$prop->id)->update([
                    'project_id'=>$request->project_id,
                    'project_unit_id'=>$unitId,
                    'tower'=>$request->tower,
                    'unit_number'=>$request->unit_number,
                ]);
            }
        }

        // Save parameters (bedrooms, bathrooms etc)
        foreach ($parameters as $param) {
            $fieldName = 'par_' . $param->id;
            if ($this->categoryAllowsParameter($categoryProfile, $param) && $request->filled($fieldName)) {
                $ap = new AssignParameters();
                $ap->modal()->associate($prop);
                $ap->property_id   = $prop->id;
                $ap->parameter_id  = $param->id;
                $ap->value         = $request->input($fieldName);
                $ap->save();
            }
        }

        // Save outdoor facilities
        $facilities = DB::table('outdoor_facilities')->get();
        foreach ($facilities as $fac) {
            $fieldName = 'facility_' . $fac->id;
            if ($request->filled($fieldName)) {
                DB::table('assigned_outdoor_facilities')->insert([
                    'property_id' => $prop->id,
                    'facility_id' => $fac->id,
                    'distance'    => $request->input($fieldName),
                ]);
            }
        }

        // Gallery images (multiple)
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $img) {
                $filename = store_image($img, 'PROPERTY_GALLERY_IMG_PATH');
                PropertyImages::create([
                    'propertys_id' => $prop->id,
                    'image'        => $filename,
                ]);
            }
        }

        return redirect('/owner/my-properties')
            ->with('success', 'Property submitted! Our team will review and publish within 24 hours.');
    }

    /** Show edit form */
    public function edit($id)
    {
        $cust = $this->customer();
        $custId = $cust['id'] ?? null;
        if (!$custId) {
            return redirect('/owner/login')->with('error', 'Please login to continue.');
        }
        $prop = Property::where('id', $id)->where('added_by', $custId)->firstOrFail();

        $categories = DB::table('categories')->where('status', 1)->get();
        $parameters = DB::table('parameters')->get();
        $facilities = DB::table('outdoor_facilities')->get();

        // Saved parameters
        $savedParams = DB::table('assign_parameters')
            ->where('property_id', $id)->pluck('value', 'parameter_id');

        // Saved facilities
        $savedFacilities = DB::table('assigned_outdoor_facilities')
            ->where('property_id', $id)->pluck('distance', 'facility_id');

        // Gallery
        $gallery = PropertyImages::where('propertys_id', $id)->get();
        [$builderProjects,$builderProjectUnits] = $this->builderProjectData($cust);
        $isEdit = true;
        $formUrl = url('/owner/property/' . $id . '/update');

        return view('frontend.owner.post-property', compact(
            'cust', 'prop', 'categories', 'parameters', 'facilities',
            'savedParams', 'savedFacilities', 'gallery', 'builderProjects', 'builderProjectUnits',
            'isEdit', 'formUrl'
        ));
    }

    /** Update property */
    public function update(Request $request, $id)
    {
        $cust = $this->customer();
        $custId = $cust['id'] ?? null;
        if (!$custId) {
            return redirect('/owner/login')->with('error', 'Please login to continue.');
        }
        $prop = Property::where('id', $id)->where('added_by', $custId)->firstOrFail();

        $categoryProfile = $this->categoryProfileKey($request->category_id);
        $parameters = DB::table('parameters')->get();

        $request->validate(array_merge([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'category_id' => 'required|integer',
            'propery_type'=> 'required|in:0,1',
            'address'     => 'required|string',
            'city'        => 'required|string',
            'state'       => 'required|string',
            'price'       => 'required|numeric|min:1',
            'title_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'project_id'  => 'nullable|integer',
            'project_unit_id' => 'nullable|integer',
            'tower'       => 'nullable|string|max:80',
            'unit_number' => 'nullable|string|max:80',
        ], $this->categoryValidationRules($categoryProfile, $parameters)));

        [$categoryProfile, $categoryData] = $this->sanitizedCategoryData($request);

        $data = $request->only([
            'title','description','category_id','propery_type','sub_type','commercial_type','address','city','state',
            'country','pincode','latitude','longitude','price','total_area','carpet_area',
            'floor_number','total_floors','age_of_building','facing','furnishing',
            'water_supply','prop_status','maintenance','security_deposit','rentduration','video_link',
        ]);
        $data['sub_type'] = $categoryData['sub_type'];
        $data['commercial_type'] = $categoryData['commercial_type'];
        $data['carpet_area'] = $categoryData['carpet_area'];
        $data['floor_number'] = $categoryData['floor_number'];
        $data['total_floors'] = $categoryData['total_floors'];
        $data['age_of_building'] = $categoryData['age_of_building'];
        $data['furnishing'] = $categoryData['furnishing'];
        $data['water_supply'] = $categoryData['water_supply'];
        $data['latitude'] = (string) ($request->latitude ?? '');
        $data['longitude'] = (string) ($request->longitude ?? '');
        $data['video_link'] = $request->video_link ?? '';

        $data['client_address']  = $request->address;
        $data['price_negotiable']= $request->price_negotiable ? 1 : 0;
        $data['request_status']  = 'pending'; // re-review on edit
        $data['listing_type'] = $request->listing_mode === 'business' ? 'business' : ($prop->listing_type ?? 'property');
        $data['business_type'] = $request->listing_mode === 'business' ? $request->commercial_type : null;
        $data['business_meta'] = json_encode(array_filter([
            'property_group' => $categoryProfile, 'source_property_group' => $request->property_group, 'listing_mode' => $request->listing_mode,
            'land_type' => $request->land_type, 'area_unit' => $request->area_unit,
            'road_width' => $request->road_width, 'boundary_wall' => $request->boundary_wall,
        ]));

        if ($request->hasFile('title_image')) {
            $data['title_image'] = store_image($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
        }

        $prop->update($data);

        $projectFields=['project_id'=>null,'project_unit_id'=>null,'tower'=>null,'unit_number'=>null];
        if ($request->filled('project_id')) {
            $ownsProject=DB::table('projects')->where('id',$request->project_id)->where('added_by',$cust['id'])
                ->where('request_status','approved')->where('status',1)->exists();
            if ($ownsProject) {
                $projectFields['project_id']=$request->project_id;
                $projectFields['project_unit_id']=$request->filled('project_unit_id')
                    ? DB::table('builder_project_units')->where('id',$request->project_unit_id)->where('project_id',$request->project_id)->value('id')
                    : null;
                $projectFields['tower']=$request->tower;
                $projectFields['unit_number']=$request->unit_number;
            }
        }
        DB::table('propertys')->where('id',$prop->id)->update($projectFields);

        // Refresh parameters
        DB::table('assign_parameters')->where('property_id', $id)->delete();
        foreach ($parameters as $param) {
            $fieldName = 'par_' . $param->id;
            if ($this->categoryAllowsParameter($categoryProfile, $param) && $request->filled($fieldName)) {
                $ap = new AssignParameters();
                $ap->modal()->associate($prop);
                $ap->property_id  = $prop->id;
                $ap->parameter_id = $param->id;
                $ap->value        = $request->input($fieldName);
                $ap->save();
            }
        }

        // Refresh facilities
        DB::table('assigned_outdoor_facilities')->where('property_id', $id)->delete();
        $facilities = DB::table('outdoor_facilities')->get();
        foreach ($facilities as $fac) {
            $fieldName = 'facility_' . $fac->id;
            if ($request->filled($fieldName)) {
                DB::table('assigned_outdoor_facilities')->insert([
                    'property_id' => $prop->id,
                    'facility_id' => $fac->id,
                    'distance'    => $request->input($fieldName),
                ]);
            }
        }

        // New gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $img) {
                $filename = store_image($img, 'PROPERTY_GALLERY_IMG_PATH');
                PropertyImages::create(['propertys_id' => $prop->id, 'image' => $filename]);
            }
        }

        return redirect('/owner/my-properties')->with('success', 'Property updated successfully!');
    }

    /** Delete property */
    public function destroy($id)
    {
        $cust = $this->customer();
        $prop = Property::where('id', $id)->where('added_by', $cust['id'])->firstOrFail();

        // Remove related data
        DB::table('assign_parameters')->where('property_id', $id)->delete();
        DB::table('assigned_outdoor_facilities')->where('property_id', $id)->delete();
        DB::table('interested_users')->where('property_id', $id)->delete();
        DB::table('favourites')->where('property_id', $id)->delete();
        PropertyImages::where('propertys_id', $id)->delete();
        $prop->delete();

        return response()->json(['success' => true, 'message' => 'Property deleted successfully.']);
    }

    /** AJAX: upload single gallery image */
    public function uploadGallery(Request $request, $id)
    {
        $cust = $this->customer();
        $prop = Property::where('id', $id)->where('added_by', $cust['id'])->firstOrFail();

        $request->validate(['image' => 'required|image|max:5120']);
        $filename = store_image($request->file('image'), 'PROPERTY_GALLERY_IMG_PATH');

        $img = PropertyImages::create(['propertys_id' => $prop->id, 'image' => $filename]);

        return response()->json([
            'success'  => true,
            'id'       => $img->id,
            'image_url'=> asset('images/' . config('global.PROPERTY_GALLERY_IMG_PATH') . $filename),
        ]);
    }

    /** AJAX: delete gallery image */
    public function deleteGallery(Request $request, $imageId)
    {
        $cust = $this->customer();
        $img  = PropertyImages::findOrFail($imageId);
        $prop = Property::where('id', $img->propertys_id)->where('added_by', $cust['id'])->firstOrFail();
        $img->delete();
        return response()->json(['success' => true]);
    }
}
