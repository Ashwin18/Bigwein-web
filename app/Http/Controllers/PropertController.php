<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Customer;


use App\Models\Property;
use App\Models\CityImage;
use App\Models\parameter;
use App\Models\Usertokens;
use Illuminate\Support\Str;
use App\Models\RejectReason;
use Illuminate\Http\Request;

use App\Models\Notifications;
use App\Models\PropertyImages;
use App\Services\HelperService;
use App\Models\AssignParameters;
use App\Models\OutdoorFacilities;
use App\Services\ResponseService;
use App\Models\PropertiesDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\BootstrapTableService;
use App\Models\AssignedOutdoorFacilities;
use Illuminate\Support\Facades\Validator;


class PropertController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!has_permissions('read', 'property')) {
            return redirect()->back()->with('error', trans(PERMISSION_ERROR_MSG));
        } else {
            $customerID = $_GET['customer'] ?? null;
            $category = Category::all();
            $approvalCounts = [
                'pending' => Property::where('request_status', 'pending')->count(),
                'approved' => Property::where('request_status', 'approved')->count(),
                'rejected' => Property::where('request_status', 'rejected')->count(),
                'total' => Property::count(),
            ];
            return view('property.index', compact('category','customerID','approvalCounts'));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!has_permissions('create', 'property')) {
            return redirect()->back()->with('error', trans(PERMISSION_ERROR_MSG));
        } else {
            $category = Category::where('status', '1')->get();
            $parameters = parameter::all();
            $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();
            $facility = OutdoorFacilities::all();
            $distanceValueDB = system_setting('distance_option');
            $distanceValue = isset($distanceValueDB) && !empty($distanceValueDB) ? $distanceValueDB : 'km';
            $languages = HelperService::getActiveLanguages();
            return view('property.create', compact('category', 'parameters', 'currency_symbol', 'facility', 'distanceValue','languages'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $arr = [];
        if (!has_permissions('create', 'property')) {
            return redirect()->back()->with('error', trans(PERMISSION_ERROR_MSG));
        } else {
            $request->validate([
                'slug'              => 'nullable|regex:/^[a-z0-9-]+$/|unique:propertys,slug_id',
                'gallery_images.*'  => 'required|image|mimes:jpg,png,jpeg|max:2048',
                'documents.*'       => 'nullable|mimes:pdf,doc,docx,txt|max:5120',
                'title_image'       => 'required|image|mimes:jpg,png,jpeg|max:2048',
                'meta_title'        => 'nullable|max:255',
                'meta_image'        => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
                'meta_description'  => 'nullable|max:255',
                'meta_keywords'     => 'nullable|max:255',
                'price'             => 'required|numeric|min:1|max:9223372036854775807',
                'video_link' => ['nullable', 'url', function ($attribute, $value, $fail) {
                    $youtubePattern = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/(watch\?v=|embed\/|shorts\/)?([a-zA-Z0-9_-]{11})([\/\?\&\=\w\-]*)?$/';

                    if (!preg_match($youtubePattern, $value)) {
                        $headers = @get_headers($value);
                        if (!$headers || strpos($headers[0], '200') === false) {
                            return $fail(trans('Invalid Video Link'));
                        }
                        return $fail(trans('Invalid Video Link'));
                    }
                }]


            ], [], [
                'documents.*' => 'document :position'
            ]);

            try {
                DB::beginTransaction();

                $saveProperty = new Property();
                $saveProperty->category_id = $request->category;
                $saveProperty->title = $request->title;
                $saveProperty->slug_id = $request->slug ?? generateUniqueSlug($request->title,1);
                $saveProperty->description = $request->description;
                $saveProperty->address = $request->address;
                $saveProperty->client_address = $request->client_address;
                $saveProperty->propery_type = $request->property_type;
                $saveProperty->price = $request->price;
                $saveProperty->request_status = "approved";
                $saveProperty->package_id = 0;
                $saveProperty->city = (isset($request->city)) ? $request->city : '';
                $saveProperty->country = (isset($request->country)) ? $request->country : '';
                $saveProperty->state = (isset($request->state)) ? $request->state : '';
                $saveProperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
                $saveProperty->longitude = (isset($request->longitude)) ? $request->longitude : '';
                $saveProperty->video_link = (isset($request->video_link)) ? $request->video_link : '';
                $saveProperty->post_type = 0;
                $saveProperty->added_by = 0;
                $saveProperty->meta_title = isset($request->meta_title) ? $request->meta_title : $request->title;
                $saveProperty->meta_description = $request->meta_description;
                $saveProperty->meta_keywords = $request->keywords;
                $saveProperty->rentduration = $request->price_duration;
                $saveProperty->is_premium = $request->is_premium;

                // ── New synced fields ──
                $saveProperty->total_area       = $request->total_area;
                $saveProperty->carpet_area      = $request->carpet_area;
                $saveProperty->floor_number     = $request->floor_number;
                $saveProperty->total_floors     = $request->total_floors;
                $saveProperty->age_of_building  = $request->age_of_building;
                $saveProperty->facing           = $request->facing;
                $saveProperty->furnishing       = $request->furnishing;
                $saveProperty->water_supply     = $request->water_supply;
                $saveProperty->prop_status      = $request->prop_status;
                $saveProperty->pincode          = $request->pincode;
                $saveProperty->maintenance      = $request->maintenance;
                $saveProperty->security_deposit = $request->security_deposit;
                $saveProperty->price_negotiable = $request->price_negotiable ? 1 : 0;

                if ($request->hasFile('title_image')) {
                    $saveProperty->title_image = store_image($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
                } else {
                    $saveProperty->title_image  = '';
                }

                if ($request->hasFile('3d_image')) {
                    $saveProperty->three_d_image = store_image($request->file('3d_image'), '3D_IMG_PATH');
                } else {
                    $saveProperty->three_d_image  = '';
                }

                if ($request->hasFile('meta_image')) {
                    $saveProperty->meta_image = store_image($request->file('meta_image'), 'PROPERTY_SEO_IMG_PATH');
                }

                $saveProperty->save();

                $facility = OutdoorFacilities::all();
                foreach ($facility as $key => $value) {
                    if ($request->has('facility' . $value->id) && $request->input('facility' . $value->id) != '') {
                        $facilities = new AssignedOutdoorFacilities();
                        $facilities->facility_id = $value->id;
                        $facilities->property_id = $saveProperty->id;
                        $facilities->distance = $request->input('facility' . $value->id);
                        $facilities->save();
                    }
                }
                $parameters = parameter::all();
                foreach ($parameters as $par) {
                    if ($request->has('par_' . $par->id)) {
                        $assign_parameter = new AssignParameters();
                        $assign_parameter->parameter_id = $par->id;
                        if (($request->hasFile('par_' . $par->id))) {
                            $allowedMimeTypes = [
                                'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp',
                                'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'
                            ];
                            $mimeType = $request->file('par_' . $par->id)->getMimeType();
                            if (!in_array($mimeType, $allowedMimeTypes)) {
                                return ResponseService::validationError(trans('The parameter file must be an image (jpeg, png, jpg, gif, webp) or a document (pdf, doc, docx, txt).'));
                            }
                            $destinationPath = public_path('images') . config('global.PARAMETER_IMG_PATH');
                            if (!is_dir($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }
                            $imageName = microtime(true) . "." . ($request->file('par_' . $par->id))->getClientOriginalExtension();
                            ($request->file('par_' . $par->id))->move($destinationPath, $imageName);
                            $assign_parameter->value = $imageName;
                        } else {
                            $assign_parameter->value = is_array($request->input('par_' . $par->id)) ? json_encode($request->input('par_' . $par->id), JSON_FORCE_OBJECT) : (!empty($request->input('par_' . $par->id)) ? $request->input('par_' . $par->id) : null);
                        }
                        $assign_parameter->modal()->associate($saveProperty);
                        $assign_parameter->save();
                        $arr = $arr + [$par->id => $request->input('par_' . $par->id)];
                    }
                }

                /// START :: UPLOAD GALLERY IMAGE
                $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $saveProperty->id;
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                if ($request->hasfile('gallery_images')) {
                    foreach ($request->file('gallery_images') as $file) {
                        $name = microtime(true) . '.' . $file->extension();
                        $file->move($destinationPath, $name);
                        PropertyImages::create([
                            'image' => $name,
                            'propertys_id' => $saveProperty->id
                        ]);
                    }
                }
                /// END :: UPLOAD GALLERY IMAGE


                /// START :: UPLOAD DOCUMENT
                $destinationPath = public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . "/" . $saveProperty->id;
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                if ($request->hasFile('documents')) {
                    $documentsData = array();
                    foreach ($request->file('documents') as $file) {
                        $type = $file->extension();
                        $name = microtime(true). '.' . $type;
                        $file->move($destinationPath, $name);

                        $documentsData[] = array(
                            'property_id'   => $saveProperty->id,
                            'name'          => $name,
                            'type'          => $type
                        );
                    }

                    if(collect($documentsData)->isNotEmpty()){
                        PropertiesDocument::insert($documentsData);
                    }
                }
                /// END :: UPLOAD DOCUMENT

                // START :: ADD CITY DATA
                if(isset($request->city) && !empty($request->city)){
                    CityImage::updateOrCreate(array('city' => $request->city));
                }
                // END :: ADD CITY DATA

                // START ::Add Translations
                if(isset($request->translations) && !empty($request->translations)){
                    $translationData = array();
                    foreach($request->translations as $translation){
                        foreach($translation as $key => $value){
                            $translationData[] = array(
                                'translatable_id'   => $saveProperty->id,
                                'translatable_type' => 'App\Models\Property',
                                'language_id'       => $value['language_id'],
                                'key'               => $key,
                                'value'             => $value['value'],
                            );
                        }
                    }
                    if(!empty($translationData)){
                        HelperService::storeTranslations($translationData);
                    }
                }

                // END ::Add Translations

                DB::commit();
                ResponseService::successResponse('Data Created Successfully');
            } catch (Exception $e) {
                DB::rollBack();
                ResponseService::logErrorResponse($e,"Create Property Issue");
            }
        }
    }


    /**
     * Stable admin review endpoint for owner-submitted properties.
     * Keeps compatibility with existing property-approval/{id}/detail routes.
     */
    public function show($id)
    {
        return app(\App\Http\Controllers\PropertyApprovalController::class)->detail((int) $id);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!has_permissions('update', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $category = Category::all()->where('status', '1')->mapWithKeys(function ($item, $key) {
                return [$item['id'] => $item['category']];
            });
            $category = Category::where('status', '1')->get();
            $list = Property::with(['assignParameter' => function($q){
                $q->with('parameter:id,name,type_of_parameter,type_values,is_required,image')->select('id','modal_type','modal_id','property_id','parameter_id','value');
            },'translations'])->where('id', $id)->get()->first();

            $categoryData = Category::find($list->category_id);

            $categoryParameterTypeIds = explode(',', $categoryData['parameter_types']);

            $parameters = parameter::all();
            $edit_parameters = parameter::with(['assigned_parameter' => function ($q) use ($id) {
                $q->where('modal_id', $id)->select('id','modal_type','modal_id','property_id','parameter_id','value');
            }])->whereIn('id',$categoryParameterTypeIds)->get();

            // Sort the collection by the order of IDs in $categoryParameterTypeIds.
            $edit_parameters = $edit_parameters->sortBy(function ($parameter) use ($categoryParameterTypeIds) {
                return array_search($parameter->id, $categoryParameterTypeIds);
            });

            // Reset the keys on the sorted collection.
            $edit_parameters = $edit_parameters->values();




            $facility = OutdoorFacilities::with(['assign_facilities' => function ($q) use ($id) {
                $q->where('property_id', $id)->select('id','property_id','facility_id','distance');
            }])->get();

            $assignFacility = AssignedOutdoorFacilities::where('property_id', $id)->get();

            $arr = json_decode($list->carpet_area);
            $par_arr = [];
            $par_id = [];
            $type_arr = [];
            foreach ($list->assignParameter as  $par) {
                $par_arr = $par_arr + [$par->parameter->name => $par->value];
                $par_id = $par_id + [$par->parameter->name => $par->value];
            }
            $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();
            $distanceValueDB = system_setting('distance_option');
            $distanceValue = isset($distanceValueDB) && !empty($distanceValueDB) ? $distanceValueDB : 'km';
            $languages = HelperService::getActiveLanguages();
            return view('property.edit', compact('category', 'facility', 'assignFacility', 'edit_parameters', 'list', 'id', 'par_arr', 'parameters', 'par_id', 'currency_symbol','distanceValue','languages'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!has_permissions('update', 'property')) {
            return redirect()->back()->with('error', trans(PERMISSION_ERROR_MSG));
        } else {
            $request->validate([
                'slug'                      => 'nullable|regex:/^[a-z0-9-]+$/|unique:propertys,slug_id,'.$id.',id',
                'gallery_images.*'          => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
                'documents.*'               => 'nullable|mimes:pdf,doc,docx,txt|max:5120',
                'title_image'               => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
                'edit_meta_title'           => 'nullable|max:255',
                'meta_image'                => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
                'edit_meta_description'     => 'nullable|max:255',
                'Keywords'                  => 'nullable|max:255',
                'price'                     => 'required|numeric|min:1|max:9223372036854775807',
                'video_link'                => ['nullable', 'url', function ($attribute, $value, $fail) {
                    $youtubePattern = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/(watch\?v=|embed\/|shorts\/)?([a-zA-Z0-9_-]{11})([\/\?\&\=\w\-]*)?$/';

                    if (!preg_match($youtubePattern, $value)) {
                        $headers = @get_headers($value);
                        if (!$headers || strpos($headers[0], '200') === false) {
                            return $fail(trans('Invalid Video Link'));
                        }
                        return $fail(trans('Invalid Video Link'));
                    }
                }]

            ],[], [
                'documents.*' => 'document :position'
            ]);

            try{

                DB::beginTransaction();
                $UpdateProperty = Property::with('assignparameter.parameter')->find($id);
                $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $UpdateProperty->category_id = $request->category;
                $UpdateProperty->title = $request->title;
                $UpdateProperty->slug_id = $request->slug ?? generateUniqueSlug($request->title,1,null,$id);
                $UpdateProperty->description = $request->description;
                $UpdateProperty->address = $request->address;
                $UpdateProperty->client_address = $request->client_address;
                $UpdateProperty->propery_type = $request->property_type;
                $UpdateProperty->price = $request->price;
                $UpdateProperty->propery_type = $request->property_type;
                $UpdateProperty->price = $request->price;
                $UpdateProperty->state = (isset($request->state)) ? $request->state : '';
                $UpdateProperty->country = (isset($request->country)) ? $request->country : '';
                $UpdateProperty->city = (isset($request->city)) ? $request->city : '';
                $UpdateProperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
                $UpdateProperty->longitude = (isset($request->longitude)) ? $request->longitude : '';
                $UpdateProperty->video_link = (isset($request->video_link)) ? $request->video_link : '';
                $UpdateProperty->is_premium = $request->is_premium;

                // ── New synced fields ──
                $UpdateProperty->total_area       = $request->total_area;
                $UpdateProperty->carpet_area      = $request->carpet_area;
                $UpdateProperty->floor_number     = $request->floor_number;
                $UpdateProperty->total_floors     = $request->total_floors;
                $UpdateProperty->age_of_building  = $request->age_of_building;
                $UpdateProperty->facing           = $request->facing;
                $UpdateProperty->furnishing       = $request->furnishing;
                $UpdateProperty->water_supply     = $request->water_supply;
                $UpdateProperty->prop_status      = $request->prop_status;
                $UpdateProperty->pincode          = $request->pincode;
                $UpdateProperty->maintenance      = $request->maintenance;
                $UpdateProperty->security_deposit = $request->security_deposit;
                $UpdateProperty->price_negotiable = $request->price_negotiable ? 1 : 0;
                $UpdateProperty->meta_title = (isset($request->edit_meta_title)) ? $request->edit_meta_title : '';
                $UpdateProperty->meta_description = (isset($request->edit_meta_description)) ? $request->edit_meta_description : '';
                $UpdateProperty->meta_keywords = (isset($request->Keywords)) ? $request->Keywords : '';

                $UpdateProperty->rentduration = $request->price_duration;
                if ($request->hasFile('title_image')) {
                    \unlink_image($UpdateProperty->title_image);
                    $UpdateProperty->title_image = \store_image($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
                }

                if ($request->hasFile('3d_image')) {
                    \unlink_image($UpdateProperty->three_d_image);
                    $UpdateProperty->three_d_image = \store_image($request->file('3d_image'), '3D_IMG_PATH');
                }

                if ($request->hasFile('meta_image')) {
                    \unlink_image($UpdateProperty->meta_image);
                    $UpdateProperty->meta_image = \store_image($request->file('meta_image'), 'PROPERTY_SEO_IMG_PATH');
                }

                $UpdateProperty->update();
                AssignedOutdoorFacilities::where('property_id', $UpdateProperty->id)->delete();
                $facility = OutdoorFacilities::all();
                foreach ($facility as $key => $value) {
                    if ($request->has('facility' . $value->id) && $request->input('facility' . $value->id) != '') {
                        $facilities = new AssignedOutdoorFacilities();
                        $facilities->facility_id = $value->id;
                        $facilities->property_id = $UpdateProperty->id;
                        $facilities->distance = $request->input('facility' . $value->id);
                        $facilities->save();
                    }
                }
                $parameters = parameter::all();

                AssignParameters::where('modal_id', $id)->delete();
                foreach ($parameters as $par) {
                    if ($request->has('par_' . $par->id)) {
                        $update_parameter = new AssignParameters();
                        $update_parameter->parameter_id = $par->id;
                        if (($request->hasFile('par_' . $par->id))) {
                            $allowedMimeTypes = [
                                'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp',
                                'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'
                            ];
                            $mimeType = $request->file('par_' . $par->id)->getMimeType();
                            if (!in_array($mimeType, $allowedMimeTypes)) {
                                return ResponseService::validationError(trans('The parameter file must be an image (jpeg, png, jpg, gif, webp) or a document (pdf, doc, docx, txt).'));
                            }
                            $update_parameter->value = \store_image($request->file('par_' . $par->id), 'PARAMETER_IMG_PATH');
                        } else {
                            $update_parameter->value = is_array($request->input('par_' . $par->id)) ? json_encode($request->input('par_' . $par->id), JSON_FORCE_OBJECT) : (!empty($request->input('par_' . $par->id)) ? $request->input('par_' . $par->id) : null);
                        }
                        $update_parameter->modal()->associate($UpdateProperty);
                        $update_parameter->save();
                    }
                }

                /// START :: UPLOAD GALLERY IMAGE
                $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
                if (!is_dir($FolderPath)) {
                    mkdir($FolderPath, 0777, true);
                }

                $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $UpdateProperty->id;

                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                if ($request->hasfile('gallery_images')) {
                    foreach ($request->file('gallery_images') as $file) {
                        $name = microtime(true) . '.' . $file->extension();
                        $file->move($destinationPath, $name);

                        PropertyImages::create([
                            'image' => $name,
                            'propertys_id' => $UpdateProperty->id
                        ]);
                    }
                }
                /// END :: UPLOAD GALLERY IMAGE


                /// START :: UPLOAD DOCUMENT
                $destinationPath = public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . "/" . $UpdateProperty->id;
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                if ($request->hasFile('documents')) {
                    $documentsData = array();
                    foreach ($request->file('documents') as $file) {
                        $type = $file->extension();
                        $name = microtime(true). '.' . $type;
                        $file->move($destinationPath, $name);

                        $documentsData[] = array(
                            'property_id'   => $UpdateProperty->id,
                            'name'          => $name,
                            'type'          => $type
                        );
                    }

                    if(collect($documentsData)->isNotEmpty()){
                        PropertiesDocument::insert($documentsData);
                    }
                }
                /// END :: UPLOAD DOCUMENT

                // START :: ADD CITY DATA
                if(isset($request->city) && !empty($request->city)){
                    CityImage::updateOrCreate(array('city' => $request->city));
                }
                // END :: ADD CITY DATA

                // START ::Add Translations
                if(isset($request->translations) && !empty($request->translations)){
                    $translationData = array();
                    foreach($request->translations as $translation){
                        foreach($translation as $key => $value){
                            if(is_array($value)){
                                $translationData[] = array(
                                    'id'                => $value['id'] ?? null,
                                    'translatable_id'   => $UpdateProperty->id,
                                    'translatable_type' => 'App\Models\Property',
                                    'language_id'       => $value['language_id'],
                                    'key'               => $key,
                                    'value'             => $value['value'],
                                );
                            }
                        }
                    }
                    if(!empty($translationData)){
                        HelperService::storeTranslations($translationData);
                    }
                }

                DB::commit();
                ResponseService::successResponse('Data Updated Successfully');
            } catch (Exception $e) {
                DB::rollBack();
                ResponseService::logErrorResponse($e,"Update Property Issue");
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (env('DEMO_MODE') && Auth::user()->email != "superadmin@gmail.com") {
            return redirect()->back()->with('error', trans('This is not allowed in the Demo Version'));
        }
        if (!has_permissions('delete', 'property')) {
            return redirect()->back()->with('error', trans(PERMISSION_ERROR_MSG));
        } else {
            DB::beginTransaction();
            $property = Property::find($id);

            if ($property->delete()) {
                DB::commit();
                ResponseService::successRedirectResponse('Data Deleted Successfully');
            } else {
                DB::rollBack();
                ResponseService::errorRedirectResponse('Something Wrong');
            }
        }
    }



    public function getPropertyList(Request $request)
    {

        $offset = (int) $request->input('offset', 0); // Ensure integer for pagination
        $limit = (int) $request->input('limit', 10);   // Ensure integer for pagination
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $customerID = $request->input('customerID', null);

        $sql = Property::with('category')
            ->with('customer:id,name,mobile')
            ->with('assignParameter.parameter')
            ->with('interested_users')
            ->with('advertisement')
            ->orderBy($sort, $order);

        $searchQuery = null;
        $propertyType = null;
        $status = null;
        $categoryId = null;
        $propertyAddedBy = null;
        $requestStatus = $request->input('request_status', null);

        // Extract and validate filters
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchQuery = trim($_GET['search']);  // Trim whitespace
        }

        if (isset($_GET['property_type']) && $_GET['property_type'] !== "") {
            $propertyType = $_GET['property_type'];
        }

        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $status = $_GET['status'];
        }

        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $categoryId = (int) $_GET['category']; // Ensure integer for category ID
        }

        if (isset($_GET['property_added_by']) && $_GET['property_added_by'] !== '') {
            $propertyAddedBy = $_GET['property_added_by']; // Ensure integer for category ID
        }
        if (isset($_GET['property_accessibility']) && $_GET['property_accessibility'] !== '') {
            $propertyAccessibility = $_GET['property_accessibility']; // Ensure integer for category ID
        }

        // Apply filters with proper escaping for security
        if ($searchQuery !== null) {
            $sql = $sql->where(function ($query) use ($searchQuery) {
                $query->where('id', 'LIKE', "%$searchQuery%")->orwhere('title', 'LIKE', "%$searchQuery%")->orwhere('address', 'LIKE', "%$searchQuery%");
                $query->orWhereHas('category', function ($query) use ($searchQuery) {
                    $query->where('category', 'LIKE', "%$searchQuery%");
                })->orWhereHas('customer', function ($query) use ($searchQuery) {
                    $query->where('name', 'LIKE', "%$searchQuery%")->orwhere('email', 'LIKE', "%$searchQuery%");
                });
            });
        }

        if ($propertyType !== null) {
            $sql = $sql->where('propery_type', $propertyType);
        }

        if (!empty($customerID)) {
            $sql = $sql->where('added_by',$customerID);
        }

        if ($status !== null) {
            $sql = $sql->where('status', $status);
        }

        if ($requestStatus !== null && $requestStatus !== '') {
            $sql = $sql->where('request_status', $requestStatus);
        }

        if ($categoryId !== null) {
            $sql = $sql->where('category_id', $categoryId);
        }

        if ($propertyAddedBy !== null) {
            if($propertyAddedBy == 0){
                $sql = $sql->where('added_by', 0);
            }else{
                $sql = $sql->whereNot('added_by', 0);
            }
        }
        if (isset($propertyAccessibility) && $propertyAccessibility !== null) {
            if($propertyAccessibility == 1){
                $sql = $sql->where('is_premium', 1);
            }else{
                $sql = $sql->where('is_premium', 0);
            }
        }

        $total = $sql->count();

        if (isset($limit)) {
            $sql = $sql->skip($offset)->take($limit);
        }

        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        $count = 1;

        $operate = '';
        $currency_symbol = Setting::where('type', 'currency_symbol')->pluck('data')->first();

        foreach ($res as $row) {
            $tempRow = $row->toArray();
            $tempRow['property_type_raw'] = $row->getRawOriginal('propery_type');
            $tempRow['active_state'] = $row->status;

            if($row->added_by == 0){
                if (has_permissions('update', 'property')) {
                    $operate = BootstrapTableService::editButton(route('property.edit', $row->id), false);
                }
                if (has_permissions('delete', 'property')) {
                    $operate .= BootstrapTableService::deleteButton(route('property.destroy', $row->id));
                }
            }else{
                if (has_permissions('update', 'property')) {
                    $reviewUrl = url('property-approval/'.$row->id.'/detail');
                    $operate = '<div class="d-flex flex-wrap gap-1 justify-content-center">';
                    $operate .= '<a href="'.$reviewUrl.'" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Review"><i class="bi bi-eye"></i> Review</a>';
                    if ($row->request_status === 'pending') {
                        $operate .= '<button type="button" class="btn btn-sm btn-success rounded-pill px-2 quick-approve-btn" data-id="'.$row->id.'" data-title="'.e($row->title).'" title="Approve"><i class="bi bi-check-lg"></i> Approve</button>';
                        $operate .= '<button type="button" class="btn btn-sm btn-danger rounded-pill px-2 request-status-btn" id="'.$row->id.'" data-title="'.e($row->title).'" data-toggle="modal" data-bs-target="#changeRequestStatusModal" data-bs-toggle="modal" title="Reject"><i class="bi bi-x-lg"></i> Reject</button>';
                    }
                    $operate .= '</div>';
                }
                if (has_permissions('delete', 'property')) {
                    $operate .= BootstrapTableService::deleteButton(route('property.destroy', $row->id));
                }
            }
            $onlyDeleteOperate = BootstrapTableService::deleteButton(route('property.destroy', $row->id));

            $interested_users = array();
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    array_push($interested_users, $interested_user->customer_id);
                }
            }

            $price = null;
            if(!empty($row->propery_type) && $row->getRawOriginal('propery_type') == 1){
                $price = !empty($row->rentduration) ?  $currency_symbol . '' . $row->price . '/' . $row->rentduration : $row->price;
            }else{
                $price = $currency_symbol . '' .$row->price;
            }

            $tempRow['total_interested_users'] = count($interested_users);
            $tempRow['promoted'] = $row->is_promoted;
            if($row->added_by == 0 && $row->request_status == "approved"){
                $tempRow['edit_status'] = $row->status;
            }else{
                $tempRow['edit_status'] = null;
            }
            if(has_permissions('update', 'property')){
                $tempRow['edit_status_url'] = $row->added_by == 0 && $row->request_status == "approved" ? 'updatepropertystatus' : null;
            }else{
                $tempRow['edit_status_url'] = null;
            }
            $tempRow['price'] = $price;
            $featured = count($row->advertisement) ? '<div class="featured_tag"><div class="featured_lable">'.trans("Featured").'</div></div>' : '';
            $tempRow['Property_name'] = '<div class="propetrty_name d-flex"><img class="property_image" alt="" src="' . $row->title_image . '"><div class="property_detail"><div class="property_title">' . $row->title . '</div>' . $featured . '</div></div></div>';

            if ($row->added_by != 0) {
                $owner = $row->customer;
                $tempRow['added_by'] = $owner ? ($owner->name ?: trans('Owner unavailable')) : trans('Owner unavailable');
                $ownerMobile = $owner ? ($owner->mobile ?: '-') : '-';
                $tempRow['mobile'] = (env('DEMO_MODE')
                    ? (env('DEMO_MODE') == true && Auth::user()->email == 'superadmin@gmail.com'
                        ? $ownerMobile
                        : '****************************')
                    : $ownerMobile);
            }
            if ($row->added_by == 0) {
                $mobile = Setting::where('type', 'company_tel1')->pluck('data');
                $tempRow['added_by'] = trans('Admin');
                $tempRow['mobile'] =   $mobile[0];
            }
            $tempRow['customer_ids'] = $interested_users;

            // Interested Users
            $count = "  " . count($interested_users);
            $interestedUserButton = BootstrapTableService::editButton('', true, null, 'text-secondary', $row->id, null, '', 'bi bi-eye-fill edit_icon', $count);
            $tempRow['interested_users'] = $interestedUserButton;
            foreach ($row->interested_users as $interested_user) {
                if ($interested_user->property_id == $row->id) {
                    $tempRow['interested_users_details'] = Customer::Where('id', $interested_user->customer_id)->get()->toArray();
                }
            }

            // Gallery Images — V5.6: server-resolved paths; no legacy path guessing.
            $galleryPayload = [];
            $galleryBase = trim((string) config('global.PROPERTY_GALLERY_IMG_PATH', 'property_gallery_img/'), '/');

            foreach ($row->gallery as $galleryImage) {
                if (empty($galleryImage->image)) {
                    continue;
                }

                $imageUrl = null;
                $imageCandidates = [
                    'images/'.$galleryBase.'/'.$row->id.'/'.$galleryImage->image,
                    'images/'.$galleryBase.'/'.$galleryImage->image,
                    'images/property_gallery_img/'.$row->id.'/'.$galleryImage->image,
                    'images/property_gallery_img/'.$galleryImage->image,
                ];

                foreach (array_unique($imageCandidates) as $imageCandidate) {
                    $imageCandidate = preg_replace('#/+#', '/', $imageCandidate);
                    if (file_exists(public_path($imageCandidate))) {
                        $imageUrl = url($imageCandidate);
                        break;
                    }
                }

                $galleryPayload[] = [
                    'url' => $imageUrl,
                    'name' => (string) $galleryImage->image,
                ];
            }

            $galleryImagesCount = count($galleryPayload);
            $galleryJson = htmlspecialchars(json_encode($galleryPayload), ENT_QUOTES, 'UTF-8');

            $galleryJs = "var modal=document.getElementById('galleryImagesModal');"
                ."if(!modal){return false;}"
                ."var body=modal.querySelector('.modal-body');"
                ."if(!body){return false;}"
                ."var items=[];try{items=JSON.parse(this.getAttribute('data-images')||'[]');}catch(e){}"
                ."body.innerHTML='';"
                ."var grid=document.createElement('div');"
                ."grid.style.display='grid';grid.style.gridTemplateColumns='repeat(auto-fill,minmax(150px,1fr))';grid.style.gap='16px';"
                ."if(!items.length){var empty=document.createElement('div');empty.style.gridColumn='1/-1';empty.style.padding='28px';empty.style.textAlign='center';empty.style.color='#7b8798';empty.textContent='No gallery images uploaded.';grid.appendChild(empty);}"
                ."items.forEach(function(item){"
                    ."if(item.url){"
                        ."var link=document.createElement('a');link.href=item.url;link.target='_blank';link.rel='noopener';"
                        ."link.style.display='block';link.style.border='1px solid #e5e7eb';link.style.borderRadius='12px';link.style.overflow='hidden';link.style.background='#f8fafc';"
                        ."var img=document.createElement('img');img.src=item.url;img.alt='Gallery';img.style.width='100%';img.style.height='120px';img.style.objectFit='cover';img.style.display='block';"
                        ."img.onerror=function(){link.innerHTML='';var x=document.createElement('div');x.style.padding='28px';x.style.textAlign='center';x.style.color='#94a3b8';x.textContent='Image unavailable';link.appendChild(x);};"
                        ."link.appendChild(img);grid.appendChild(link);"
                    ."}else{"
                        ."var miss=document.createElement('div');miss.style.border='1px dashed #cbd5e1';miss.style.borderRadius='12px';miss.style.minHeight='120px';miss.style.display='flex';miss.style.alignItems='center';miss.style.justifyContent='center';miss.style.textAlign='center';miss.style.color='#94a3b8';miss.style.padding='12px';miss.textContent='Image file missing: '+(item.name||'');grid.appendChild(miss);"
                    ."}"
                ."});"
                ."body.appendChild(grid);"
                ."if(window.bootstrap&&bootstrap.Modal){bootstrap.Modal.getOrCreateInstance(modal).show();}"
                ."else if(window.jQuery&&jQuery.fn.modal){jQuery(modal).modal('show');}"
                ."else{modal.style.display='block';modal.classList.add('show');}"
                ."return false;";

            $galleryImagesButton = '<button type="button" class="btn icon btn-primary btn-sm rounded-pill bw-gallery-direct-btn"'
                .' title="'.e(trans('Gallery Images')).'"'
                .' data-images="'.$galleryJson.'"'
                .' onclick="'.htmlspecialchars($galleryJs, ENT_QUOTES, 'UTF-8').'">'
                .'<i class="bi bi-eye-fill ml-2"></i> '.$galleryImagesCount
                .'</button>';

            $tempRow['gallery-images-btn'] = $galleryImagesButton;


            // Documents
            $documentsButtonCustomClasses = ["btn","icon","btn-primary","btn-sm","rounded-pill","documents-btn"];
            $documentsButtonCustomAttributes = ["id" => $row->id, "title" => trans('Documents'), "data-toggle" => "modal", "data-bs-target" => "#documentsModal", "data-bs-toggle" => "modal"];
            $documentsCount = count($row->documents);
            $documentsButton = BootstrapTableService::button('bi bi-eye-fill', '',$documentsButtonCustomClasses,$documentsButtonCustomAttributes,$documentsCount);
            $tempRow['documents-btn'] = $documentsButton;


            $tempRow['operate'] = $operate;
            $tempRow['only_delete_operate'] = $onlyDeleteOperate;
            $rows[] = $tempRow;
            $count++;
        }
        // $cities =  json_decode(file_get_contents(public_path('json') . "/cities.json"), true);

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    public function updateStatus(Request $request)
    {
        if (!has_permissions('update', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            Property::where('id', $request->id)->update(['status' => $request->status]);
            $Property = Property::with('customer')->find($request->id);

            if($request->status == 1){
                HelperService::AlertUserForNewListing($Property->id);
            }

            if (!empty($Property->customer)) {
                if ($Property->customer->isActive == 1 && $Property->customer->notification == 1) {

                    $fcm_ids = array();
                    $user_token = Usertokens::where('customer_id', $Property->customer->id)->pluck('fcm_id')->toArray();
                    $fcm_ids[] = !empty($user_token) ? $user_token : array();

                    $msg = "";
                    if (!empty($fcm_ids)) {
                        $msg = $Property->status == 1 ? 'Activated now by Administrator ' : 'Deactivated now by Administrator ';
                        $registrationIDs = $fcm_ids[0];

                        $fcmMsg = array(
                            'title' =>  $Property->name . ' Property Updated',
                            'message' => 'Your Property Post ' . $msg,
                            'type' => 'property_inquiry',
                            'body' => 'Your Property Post ' . $msg,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound' => 'default',
                            'id' => (string)$Property->id,

                        );
                        send_push_notification($registrationIDs, $fcmMsg);
                    }
                    //END ::  Send Notification To Customer

                    Notifications::create([
                        'title' => $Property->name . ' Property Updated',
                        'message' => 'Your Property Post ' . $msg,
                        'image' => '',
                        'type' => '1',
                        'send_type' => '0',
                        'customers_id' => $Property->customer->id,
                        'propertys_id' => $Property->id
                    ]);
                }
            }
            $response['error'] = false;
            ResponseService::successResponse($request->status ? "Property Activated Successfully" : "Property Deactivated Successfully");
        }
    }



    public function removeGalleryImage(Request $request)
    {
        if (!has_permissions('delete', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
            return;
        }

        $getImage = PropertyImages::where('id', $request->id)->first();

        if (!$getImage) {
            return response()->json([
                'error' => true,
                'message' => 'Gallery image not found.'
            ], 404);
        }

        $image = $getImage->image;
        $propertys_id = $getImage->propertys_id;

        $getImage->delete();

        $galleryBase = trim((string) config('global.PROPERTY_GALLERY_IMG_PATH', 'property_gallery_img/'), '/');
        $candidates = [
            public_path('images/'.$galleryBase.'/'.$propertys_id.'/'.$image),
            public_path('images/'.$galleryBase.'/'.$image),
            public_path('images/property_gallery_img/'.$propertys_id.'/'.$image),
            public_path('images/property_gallery_img/'.$image),
        ];

        foreach (array_unique($candidates) as $candidate) {
            if (is_file($candidate)) {
                @unlink($candidate);
            }
        }

        $folder = public_path('images/'.$galleryBase.'/'.$propertys_id);
        if (is_dir($folder)) {
            $remaining = array_diff(scandir($folder), ['.', '..']);
            if (count($remaining) === 0) {
                @rmdir($folder);
            }
        }

        return response()->json([
            'error' => false,
            'message' => 'Gallery image deleted successfully.'
        ]);
    }



    public function getFeaturedPropertyList()
    {

        $offset = 0;
        $limit = 4;
        $sort = 'id';
        $order = 'DESC';

        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }

        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }

        $sql = Property::with('category')->with('customer')->whereHas('advertisement')->orderBy($sort, $order);

        $sql->skip($offset)->take($limit);

        $res = $sql->get();

        $bulkData = array();

        $rows = array();
        $tempRow = array();
        $count = 1;


        $operate = '';

        foreach ($res as $row) {

            if (count($row->advertisement)) {
                if (has_permissions('update', 'property') && $row->added_by == 0) {
                    $operate = '<a  href="' . route('property.edit', $row->id) . '"  class="btn icon btn-primary btn-sm rounded-pill mt-2" id="edit_btn" title="Edit"><i class="fa fa-edit edit_icon"></i></a>';
                }else{
                    $operate = "-";
                }
                $tempRow = $row->toArray();
                $tempRow['type'] = ucfirst($row->propery_type);
                if($row->added_by == 0 && $row->request_status == "approved"){
                    $tempRow['status'] = $row->status;
                }else{
                    $tempRow['status'] = null;
                }
                $tempRow['edit_status_url'] = 'updatepropertystatus';
                $tempRow['promoted'] = $row->is_promoted;
                $tempRow['operate'] = $operate;
                $rows[] = $tempRow;
                $count++;
            }
        }
        $total = $sql->count();
        $bulkData['total'] = $total;
        $bulkData['rows'] = $rows;

        return response()->json($bulkData);
    }
    public function updateaccessability(Request $request)
    {
        if (!has_permissions('update', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            Property::where('id', $request->id)->update(['is_premium' => $request->status]);
            ResponseService::successResponse("Data Updated Successfully");
        }
    }

    public function generateAndCheckSlug(Request $request){
        // Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }

        // Generate the slug or throw exception
        try {
            $title = $request->title;
            $id = $request->has('id') && !empty($request->id) ? $request->id : null;
            if($id){
                $slug = generateUniqueSlug($title,1,null,$id);
            }else{
                $slug = generateUniqueSlug($title,1);
            }
            ResponseService::successResponse("",$slug);
        } catch (Exception $e) {
            ResponseService::logErrorResponse($e, "Property Slug Generation Error", "Something Went Wrong");
        }
    }



    public function removeDocument(Request $request)
    {

        if (!has_permissions('delete', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            $id = $request->id;
            $getDocument = PropertiesDocument::where('id', $id)->first();
            if($getDocument){
                $file = $getDocument->getRawOriginal('name');
                $propertyId =  $getDocument->property_id;

                if (PropertiesDocument::where('id', $id)->delete()) {
                    if (file_exists(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $propertyId . "/" . $file)) {
                        unlink(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $propertyId . "/" . $file);
                    }
                    $response['error'] = false;
                } else {
                    $response['error'] = true;
                }

                $countImage = PropertiesDocument::where('property_id', $propertyId)->get();
                if ($countImage->count() == 0) {
                    rmdir(public_path('images') . config('global.PROPERTY_DOCUMENT_PATH') . $propertyId);
                }
                return response()->json($response);
            }
        }
    }


    public function removeThreeDImage($id,Request $request){
        if (!has_permissions('delete', 'property')) {
            ResponseService::errorResponse(PERMISSION_ERROR_MSG);
        } else {
            try {
                $propertyData = Property::findOrFail($id);
                unlink_image($propertyData->three_d_image);
                $propertyData->three_d_image = null;
                $propertyData->save();
                ResponseService::successResponse("Data Deleted Successfully");
            } catch (Exception $e) {
                ResponseService::logErrorResponse($e, "Remove ThreeD Image Error", "Something Went Wrong");
            }
        }
    }

    public function updateRequestStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_status' => 'required|in:approved,rejected',
            'reject_reason' => 'required_if:request_status,rejected|max:300'
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            if (!has_permissions('update', 'property')) {
                ResponseService::errorResponse(PERMISSION_ERROR_MSG);
            } else {
                $notifyNewListing = false;
                if($request->request_status == "rejected"){
                    RejectReason::create(array(
                        'property_id' => $request->id,
                        'reason' => $request->reject_reason
                    ));
                    $status = 0;
                }else{
                    $status = 1;
                    $notifyNewListing = true;
                }
                Property::where('id', $request->id)->update(['request_status' => $request->request_status, 'status' => $status]);
                DB::commit();

                // Send mail for property status
                try {
                    $propertyData = Property::where('id',$request->id)->select('id','title','request_status','added_by')->with('customer:id,name,email')->firstOrFail();
                    if(!empty($propertyData->customer->email)){
                        // Get Data of email type
                        $emailTypeData = HelperService::getEmailTemplatesTypes("property_status");

                        // Email Template
                        $propertyStatusTemplateData = system_setting($emailTypeData['type']);
                        $appName = env("APP_NAME") ?? "eBroker";
                        $variables = array(
                            'app_name' => $appName,
                            'user_name' => $propertyData->customer->name,
                            'property_name' => $propertyData->title,
                            'status' => $request->request_status,
                            'reject_reason' => $request->request_status == 'rejected' ? $request->reject_reason : null,
                            'email' => $propertyData->customer->email
                        );
                        if(empty($propertyStatusTemplateData)){
                            $propertyStatusTemplateData = "Property Status have been changed";
                        }
                        $propertyStatusTemplate = HelperService::replaceEmailVariables($propertyStatusTemplateData,$variables);

                        $data = array(
                            'email_template' => $propertyStatusTemplate,
                            'email' => $propertyData->customer->email,
                            'title' => $emailTypeData['title'],
                        );
                        HelperService::sendMail($data);
                    }
                } catch (Exception $e) {
                    Log::error("Something Went Wrong in Property Status Update Mail Sending");
                }



                // Send Notification
                $property = Property::with('customer:id,name,isActive,notification')->select('id','title','request_status','added_by')->find($request->id);
                $fcm_ids = array();
                if ($property->customer->isActive == 1 && $property->customer->notification == 1) {
                    $user_token = Usertokens::where('customer_id', $property->customer->id)->pluck('fcm_id')->toArray();
                }

                $fcm_ids[] = $user_token ?? array();

                $msg = "";
                if (!empty($fcm_ids)) {
                    $msg = $property->request_status == 'approved' ? 'Approved by Administrator ' : 'Rejected by Administrator ';
                    $registrationIDs = $fcm_ids[0];

                    $fcmMsg = array(
                        'title' =>  $property->title . ' Property Updated',
                        'message' => 'Your Property Post ' . $msg,
                        'type' => 'property_inquiry',
                        'body' => 'Your Property Post ' . $msg,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                        'id' => (string)$property->id,

                    );
                    send_push_notification($registrationIDs, $fcmMsg);
                }
                //END ::  Send Notification To Customer

                Notifications::create([
                    'title' => $property->name . ' Property Updated',
                    'message' => 'Your Property Post ' . $msg,
                    'image' => '',
                    'type' => '1',
                    'send_type' => '0',
                    'customers_id' => $property->customer->id,
                    'propertys_id' => $property->id
                ]);
                
                if($notifyNewListing){
                    HelperService::AlertUserForNewListing($request->id);
                }
                ResponseService::successResponse("Data Updated Successfully");
            }
        } catch (Exception $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "Update Request Status in Property", "Something Went Wrong");
        }
    }

}
