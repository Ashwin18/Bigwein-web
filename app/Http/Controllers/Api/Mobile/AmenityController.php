<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Resources\Api\Mobile\AmenityResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AmenityController extends MobileController
{
    public function index(Request $request)
    {
        try {
            $amenities = DB::table('property_amenities')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'sort_order']);

            return $this->success(AmenityResource::collection($amenities)->resolve($request));
        } catch (Throwable $e) {
            report($e);
            return $this->error('Amenities could not be loaded.', 500);
        }
    }
}
