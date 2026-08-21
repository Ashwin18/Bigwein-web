<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Requests\Api\Mobile\GalleryUploadRequest;
use App\Http\Requests\Api\Mobile\StoreOwnerPropertyRequest;
use App\Http\Requests\Api\Mobile\UpdateOwnerPropertyRequest;
use App\Http\Resources\Api\Mobile\OwnerPropertyListResource;
use App\Http\Resources\Api\Mobile\OwnerPropertyResource;
use App\Models\AssignParameters;
use App\Models\Property;
use App\Models\PropertyImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OwnerPropertyController extends MobileController
{
    public function index(Request $request)
    {
        if (!in_array($request->user()->owner_type, ['seller', 'builder'], true)) {
            return $this->error('An Owner/Seller account is required.', 403);
        }

        $validated = validator($request->all(), [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'request_status' => ['nullable', 'in:pending,approved,rejected,changes_requested'],
            'active' => ['nullable', 'in:0,1'],
            'property_type' => ['nullable', 'in:0,1'],
        ]);
        if ($validated->fails()) return $this->error($validated->errors()->first(), 422, $validated->errors()->toArray());

        $query = Property::query()
            ->where('added_by', $request->user()->id)
            ->where('post_type', 1)
            ->where(function ($q) {
                $q->whereNull('listing_type')->orWhere('listing_type', 'property');
            })
            ->with('category:id,category,image')
            ->select('propertys.*')
            ->addSelect([
                'gallery_count' => PropertyImages::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('propertys_id', 'propertys.id'),
                'review_remarks' => DB::table('reject_reasons')
                    ->select('reason')
                    ->whereColumn('property_id', 'propertys.id')
                    ->orderByDesc('id')
                    ->limit(1),
            ]);

        if ($request->filled('request_status')) $query->where('request_status', $request->request_status);
        if ($request->filled('active')) $query->where('status', (int) $request->active);
        if ($request->filled('property_type')) $query->where('propery_type', (int) $request->property_type);

        $paginator = $query->orderByDesc('updated_at')->paginate((int) $request->input('per_page', 20));

        return $this->success(
            OwnerPropertyListResource::collection($paginator->getCollection())->resolve($request),
            'Success',
            [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ]
        );
    }

    public function show(Request $request, int $id)
    {
        if (!in_array($request->user()->owner_type, ['seller', 'builder'], true)) {
            return $this->error('An Owner/Seller account is required.', 403);
        }

        $property = $this->ownedProperty($request, $id);
        if (!$property) return $this->error('Property not found.', 404);

        return $this->success((new OwnerPropertyResource($this->hydrateEditableData($property)))->resolve($request));
    }

    public function store(StoreOwnerPropertyRequest $request)
    {
        $uploaded = [];

        try {
            DB::beginTransaction();
            [$profile, $categoryData] = $this->sanitizedCategoryData($request);

            $data = $this->propertyPayload($request, $categoryData);
            $data += [
                'slug_id' => Str::slug($request->validated('title')).'-'.Str::lower(Str::random(6)),
                'added_by' => $request->user()->id,
                'post_type' => 1,
                'listing_type' => 'property',
                'request_status' => 'pending',
                'status' => 0,
                'total_click' => 0,
                'is_premium' => 0,
            ];

            $data['title_image'] = $this->storeConfiguredImage($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
            $uploaded[] = $this->configuredPath('PROPERTY_TITLE_IMG_PATH', $data['title_image']);

            if ($request->hasFile('three_d_image')) {
                $data['three_d_image'] = $this->storeConfiguredImage($request->file('three_d_image'), '3D_IMG_PATH');
                $uploaded[] = $this->configuredPath('3D_IMG_PATH', $data['three_d_image']);
            } else {
                $data['three_d_image'] = '';
            }

            $property = Property::create($data);
            $this->syncParameters($request, $property, $profile);
            $this->syncFacilities($request, $property->id);
            $this->syncAmenities($request, $property->id, true);
            $this->storeGallery($request->file('gallery', []), $property->id, $uploaded);

            DB::commit();
            return $this->success(
                (new OwnerPropertyResource($this->hydrateEditableData($property->fresh())))->resolve($request),
                'Property submitted for approval.',
                null,
                201
            );
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->cleanupFiles($uploaded);
            Log::error('Mobile owner property creation failed', ['owner_id' => $request->user()->id, 'exception' => $e]);
            return $this->error('Property could not be submitted. Please try again.', 500);
        }
    }

    public function update(UpdateOwnerPropertyRequest $request, int $id)
    {
        $property = $this->ownedProperty($request, $id);
        if (!$property) return $this->error('Property not found.', 404);

        $incomingGallery = $request->file('gallery', []);
        if (PropertyImages::where('propertys_id', $property->id)->count() + count($incomingGallery) > 20) {
            return $this->error('A property can have a maximum of 20 gallery images. Remove existing images before uploading more.', 422, [
                'gallery' => ['The existing and incoming gallery images exceed the maximum of 20.'],
            ]);
        }

        $uploaded = [];
        $oldFilesToDelete = [];

        try {
            DB::beginTransaction();
            [$profile, $categoryData] = $this->sanitizedCategoryData($request);
            $data = $this->propertyPayload($request, $categoryData);
            $data['request_status'] = 'pending';

            if ($request->hasFile('title_image')) {
                $old = $property->getRawOriginal('title_image');
                $data['title_image'] = $this->storeConfiguredImage($request->file('title_image'), 'PROPERTY_TITLE_IMG_PATH');
                $uploaded[] = $this->configuredPath('PROPERTY_TITLE_IMG_PATH', $data['title_image']);
                if ($old) $oldFilesToDelete[] = $this->configuredPath('PROPERTY_TITLE_IMG_PATH', $old);
            }

            if ($request->hasFile('three_d_image')) {
                $old = $property->getRawOriginal('three_d_image');
                $data['three_d_image'] = $this->storeConfiguredImage($request->file('three_d_image'), '3D_IMG_PATH');
                $uploaded[] = $this->configuredPath('3D_IMG_PATH', $data['three_d_image']);
                if ($old) $oldFilesToDelete[] = $this->configuredPath('3D_IMG_PATH', $old);
            }

            $property->update($data);
            $this->syncParameters($request, $property, $profile);
            $this->syncFacilities($request, $property->id);
            $this->syncAmenities($request, $property->id, false);
            $this->storeGallery($incomingGallery, $property->id, $uploaded);

            DB::commit();
            $this->cleanupFiles($oldFilesToDelete);

            return $this->success(
                (new OwnerPropertyResource($this->hydrateEditableData($property->fresh())))->resolve($request),
                'Property updated and submitted for approval.'
            );
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->cleanupFiles($uploaded);
            Log::error('Mobile owner property update failed', [
                'owner_id' => $request->user()->id,
                'property_id' => $id,
                'exception' => $e,
            ]);
            return $this->error('Property could not be updated. Please try again.', 500);
        }
    }

    public function destroy(Request $request, int $id)
    {
        $property = $this->ownedProperty($request, $id);
        if (!$property) return $this->error('Property not found.', 404);

        try {
            DB::transaction(function () use ($property) {
                DB::table('property_amenity_assignments')->where('property_id', $property->id)->delete();
                $property->delete();
            });
            return $this->success(null, 'Property deleted successfully.');
        } catch (Throwable $e) {
            report($e);
            return $this->error('Property could not be deleted. Please try again.', 500);
        }
    }

    public function uploadGallery(GalleryUploadRequest $request, int $id)
    {
        $property = $this->ownedProperty($request, $id);
        if (!$property) return $this->error('Property not found.', 404);

        $images = $request->file('gallery', []);
        if (PropertyImages::where('propertys_id', $property->id)->count() + count($images) > 20) {
            return $this->error('A property can have a maximum of 20 gallery images.', 422, [
                'gallery' => ['The existing and incoming gallery images exceed the maximum of 20.'],
            ]);
        }

        $uploaded = [];
        try {
            DB::beginTransaction();
            $records = $this->storeGallery($images, $property->id, $uploaded);
            DB::commit();

            $data = collect($records)->map(fn (PropertyImages $image) => [
                'id' => (int) $image->id,
                'image_url' => $this->galleryUrl($property->id, $image->image),
            ])->all();
            return $this->success($data, 'Gallery uploaded successfully.', null, 201);
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->cleanupFiles($uploaded);
            report($e);
            return $this->error('Gallery could not be uploaded. Please try again.', 500);
        }
    }

    public function deleteGallery(Request $request, int $id, int $imageId)
    {
        $property = $this->ownedProperty($request, $id);
        if (!$property) return $this->error('Property not found.', 404);

        $image = PropertyImages::where('id', $imageId)->where('propertys_id', $property->id)->first();
        if (!$image) return $this->error('Gallery image not found.', 404);

        $filename = basename((string) $image->getRawOriginal('image'));
        try {
            DB::transaction(fn () => $image->delete());
            $this->cleanupFiles([
                $this->galleryPath($property->id, $filename),
                public_path('images').config('global.PROPERTY_GALLERY_IMG_PATH').$filename,
            ]);
            return $this->success(null, 'Gallery image deleted successfully.');
        } catch (Throwable $e) {
            report($e);
            return $this->error('Gallery image could not be deleted. Please try again.', 500);
        }
    }

    private function ownedProperty(Request $request, int $id): ?Property
    {
        return Property::query()
            ->where('id', $id)
            ->where('added_by', $request->user()->id)
            ->where('post_type', 1)
            ->where(function ($q) {
                $q->whereNull('listing_type')->orWhere('listing_type', 'property');
            })
            ->with('category:id,category,image')
            ->first();
    }

    private function hydrateEditableData(Property $property): Property
    {
        $property->loadMissing('category:id,category,image');
        $property->amenity_ids = DB::table('property_amenity_assignments')
            ->where('property_id', $property->id)->pluck('amenity_id');
        $property->mobile_parameters = DB::table('assign_parameters as ap')
            ->leftJoin('parameters as p', 'p.id', '=', 'ap.parameter_id')
            ->where('ap.property_id', $property->id)
            ->select('ap.parameter_id', 'p.name', 'ap.value')->get();
        $property->mobile_facilities = DB::table('assigned_outdoor_facilities as af')
            ->leftJoin('outdoor_facilities as f', 'f.id', '=', 'af.facility_id')
            ->where('af.property_id', $property->id)
            ->select('af.facility_id', 'f.name', 'af.distance')->get();
        $property->review_remarks = $this->reviewRemarks($property);
        return $property;
    }

    private function reviewRemarks(Property $property): ?string
    {
        if (!in_array($property->request_status, ['rejected', 'changes_requested'], true)) return null;
        return DB::table('reject_reasons')->where('property_id', $property->id)->latest('id')->value('reason');
    }

    private function categoryProfile(int|string $categoryId): string
    {
        $name = strtolower((string) DB::table('categories')->where('id', $categoryId)->value('category'));
        if (str_contains($name, 'commercial') || str_contains($name, 'office') || str_contains($name, 'shop') || str_contains($name, 'showroom') || str_contains($name, 'warehouse') || str_contains($name, 'industrial') || str_contains($name, 'factory')) return 'commercial';
        if (str_contains($name, 'plot') || str_contains($name, 'land') || str_contains($name, 'agricultural')) return 'plot';
        if (str_contains($name, 'pg') || str_contains($name, 'hostel') || str_contains($name, 'co-living')) return 'pg';
        if (str_contains($name, 'villa') || str_contains($name, 'town') || str_contains($name, 'apartment') || str_contains($name, 'flat') || str_contains($name, 'house') || str_contains($name, 'bungalow') || str_contains($name, 'banglow') || str_contains($name, 'penthouse') || str_contains($name, 'condo')) return 'residential';
        return 'generic';
    }

    private function sanitizedCategoryData(Request $request): array
    {
        $profile = $this->categoryProfile($request->integer('category_id'));
        $data = [
            'sub_type' => $request->input('sub_type'),
            'commercial_type' => $profile === 'commercial' ? $request->input('sub_type') : null,
            'carpet_area' => $request->input('carpet_area'),
            'floor_number' => $request->input('floor_number'),
            'total_floors' => $request->input('total_floors'),
            'age_of_building' => $request->input('age_of_building'),
            'furnishing' => $request->input('furnishing'),
            'water_supply' => $request->input('water_supply'),
        ];
        if ($profile === 'plot') {
            foreach (['carpet_area', 'floor_number', 'total_floors', 'age_of_building', 'furnishing', 'water_supply'] as $field) $data[$field] = null;
        }
        return [$profile, $data];
    }

    private function propertyPayload(Request $request, array $categoryData): array
    {
        return [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $request->integer('category_id'),
            'propery_type' => (int) $request->input('property_type'),
            'sub_type' => $categoryData['sub_type'],
            'commercial_type' => $categoryData['commercial_type'],
            'address' => $request->input('address'),
            'client_address' => $request->input('address'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'country' => $request->input('country', 'India'),
            'pincode' => $request->input('pincode'),
            'latitude' => (string) $request->input('latitude', ''),
            'longitude' => (string) $request->input('longitude', ''),
            'price' => $request->input('price'),
            'total_area' => $request->input('total_area'),
            'carpet_area' => $categoryData['carpet_area'],
            'floor_number' => $categoryData['floor_number'],
            'total_floors' => $categoryData['total_floors'],
            'age_of_building' => $categoryData['age_of_building'],
            'facing' => $request->input('facing'),
            'furnishing' => $categoryData['furnishing'],
            'water_supply' => $categoryData['water_supply'],
            'prop_status' => $request->input('property_status'),
            'maintenance' => $request->input('maintenance'),
            'security_deposit' => $request->input('security_deposit'),
            'price_negotiable' => $request->boolean('price_negotiable'),
            'rentduration' => $request->input('rent_duration'),
            'video_link' => $request->input('video_link', ''),
            'business_meta' => json_encode(array_filter([
                'property_group' => $this->categoryProfile($request->integer('category_id')),
                'land_type' => $request->input('land_type'),
                'area_unit' => $request->input('area_unit'),
                'road_width' => $request->input('road_width'),
                'boundary_wall' => $request->input('boundary_wall'),
            ], fn ($value) => $value !== null && $value !== '')),
        ];
    }

    private function syncParameters(Request $request, Property $property, string $profile): void
    {
        DB::table('assign_parameters')->where('property_id', $property->id)->delete();
        $allowed = DB::table('parameters')->get()->keyBy('id');

        foreach ($request->input('parameters', []) as $parameterData) {
            $parameter = $allowed->get((int) ($parameterData['parameter_id'] ?? 0));
            $value = $parameterData['value'] ?? null;
            if (!$parameter || $value === null || $value === '' || !$this->categoryAllowsParameter($profile, $parameter)) continue;

            $assignment = new AssignParameters();
            $assignment->modal()->associate($property);
            $assignment->property_id = $property->id;
            $assignment->parameter_id = $parameter->id;
            $assignment->value = $value;
            $assignment->save();
        }
    }

    private function categoryAllowsParameter(string $profile, object $parameter): bool
    {
        $name = preg_replace('/[^a-z0-9]+/', '', strtolower((string) $parameter->name));
        $blocked = $profile === 'plot'
            ? ['bedroom', 'bathroom', 'bhk', 'balcon', 'kitchen', 'floor', 'furnish', 'ageofbuilding']
            : ($profile === 'commercial' ? ['bedroom', 'bhk', 'balcon'] : []);
        if ($profile === 'pg') $blocked[] = 'bhk';
        foreach ($blocked as $word) if (str_contains($name, $word)) return false;
        return true;
    }

    private function syncFacilities(Request $request, int $propertyId): void
    {
        DB::table('assigned_outdoor_facilities')->where('property_id', $propertyId)->delete();
        $rows = collect($request->input('facilities', []))->map(fn ($facility) => [
            'property_id' => $propertyId,
            'facility_id' => (int) $facility['facility_id'],
            'distance' => $facility['distance'],
        ])->unique('facility_id')->values()->all();
        if ($rows) DB::table('assigned_outdoor_facilities')->insert($rows);
    }

    private function syncAmenities(Request $request, int $propertyId, bool $create): void
    {
        if (!$create && !$request->exists('amenity_ids')) return;
        DB::table('property_amenity_assignments')->where('property_id', $propertyId)->delete();
        $now = now();
        $rows = collect($request->input('amenity_ids', []))->map(fn ($id) => (int) $id)->unique()->map(fn ($id) => [
            'property_id' => $propertyId,
            'amenity_id' => $id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all();
        if ($rows) DB::table('property_amenity_assignments')->insert($rows);
    }

    private function storeGallery(array $files, int $propertyId, array &$uploaded): array
    {
        $records = [];
        foreach ($files as $file) {
            $filename = microtime(true).'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension());
            $path = $this->galleryPath($propertyId, $filename);
            if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
            $file->move(dirname($path), $filename);
            $uploaded[] = $path;
            $records[] = PropertyImages::create(['propertys_id' => $propertyId, 'image' => $filename]);
        }
        return $records;
    }

    private function storeConfiguredImage($file, string $configKey): string
    {
        $dir = public_path('images').config('global.'.$configKey);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $filename = microtime(true).'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension());
        $file->move($dir, $filename);
        return $filename;
    }

    private function configuredPath(string $configKey, string $filename): string
    {
        return public_path('images').config('global.'.$configKey).basename($filename);
    }

    private function galleryPath(int $propertyId, string $filename): string
    {
        return public_path('images').config('global.PROPERTY_GALLERY_IMG_PATH').$propertyId.'/'.basename($filename);
    }

    private function galleryUrl(int $propertyId, string $filename): string
    {
        return asset('images/'.trim(config('global.PROPERTY_GALLERY_IMG_PATH'), '/').'/'.$propertyId.'/'.rawurlencode(basename($filename)));
    }

    private function cleanupFiles(array $paths): void
    {
        foreach (array_unique($paths) as $path) if (is_string($path) && is_file($path)) @unlink($path);
    }
}
