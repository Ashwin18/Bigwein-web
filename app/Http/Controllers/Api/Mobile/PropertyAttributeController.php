<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\PropertyAttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyAttributeController extends MobileController
{
    public function index(Request $request)
    {
        $validator = validator($request->all(), [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        $categoryId = $request->filled('category_id') ? (int) $request->input('category_id') : null;
        $mappingByGroup = collect();

        if ($categoryId) {
            $mappingByGroup = DB::table('property_attribute_category_map')
                ->where('category_id', $categoryId)
                ->get()->keyBy('group_id');
        }

        $query = PropertyAttributeGroup::query()
            ->where('is_active', true)
            ->with(['options' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id')]);

        if ($categoryId) {
            $query->whereIn('id', $mappingByGroup->keys());
        }

        $groups = $query->orderBy('sort_order')->orderBy('id')->get();
        if ($categoryId) {
            $groups = $groups->sortBy(fn ($group) => [
                (int) ($mappingByGroup->get($group->id)->sort_order ?? $group->sort_order),
                $group->id,
            ])->values();
        }

        $data = $groups->map(function ($group) use ($categoryId, $mappingByGroup) {
            $mapping = $categoryId ? $mappingByGroup->get($group->id) : null;
            return [
                'id' => (int) $group->id,
                'name' => $group->name,
                'code' => $group->code,
                'input_type' => $group->input_type,
                'required' => (bool) ($mapping->is_required ?? false),
                'sort_order' => (int) ($mapping->sort_order ?? $group->sort_order),
                'options' => $group->options->map(fn ($option) => [
                    'id' => (int) $option->id,
                    'name' => $option->name,
                    'value' => $option->value,
                    'sort_order' => (int) $option->sort_order,
                ])->values()->all(),
            ];
        })->values()->all();

        return $this->success($data);
    }
}
