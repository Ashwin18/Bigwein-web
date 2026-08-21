<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PropertyAttributeCategoryMap;
use App\Models\PropertyAttributeGroup;
use App\Models\PropertyAttributeOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PropertyAttributeMasterController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->check() && (int) auth()->user()->type === 0, 403, trans(PERMISSION_ERROR_MSG));
    }

    public function index()
    {
        $this->authorizeSuperAdmin();

        $groups = PropertyAttributeGroup::with(['options', 'categoryMappings.category:id,category'])
            ->orderBy('sort_order')->orderBy('id')->get();
        $categories = Category::select('id', 'category')->orderBy('category')->get();

        return view('property-attributes.index', compact('groups', 'categories'));
    }

    public function storeGroup(Request $request)
    {
        $this->authorizeSuperAdmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', 'unique:property_attribute_groups,code'],
            'input_type' => ['required', Rule::in(['single_select'])],
            'scope' => ['required', Rule::in(['category', 'global'])],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        PropertyAttributeGroup::create($data);
        return back()->with('success', 'Property attribute group added.');
    }

    public function updateGroup(Request $request, PropertyAttributeGroup $group)
    {
        $this->authorizeSuperAdmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('property_attribute_groups', 'code')->ignore($group->id)],
            'input_type' => ['required', Rule::in(['single_select'])],
            'scope' => ['required', Rule::in(['category', 'global'])],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $group->update($data);
        return back()->with('success', 'Property attribute group updated.');
    }

    public function storeOption(Request $request)
    {
        $this->authorizeSuperAdmin();
        $data = $request->validate([
            'group_id' => ['required', 'integer', 'exists:property_attribute_groups,id'],
            'name' => ['required', 'string', 'max:150'],
            'value' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['value'] = $this->normalizeValue($data['value'] ?: $data['name']);
        validator($data, [
            'value' => [Rule::unique('property_attribute_options', 'value')->where('group_id', $data['group_id'])],
        ], [], ['value' => 'normalized value'])->validate();
        $data['is_active'] = $request->boolean('is_active');
        PropertyAttributeOption::create($data);
        return back()->with('success', 'Property attribute option added.');
    }

    public function updateOption(Request $request, PropertyAttributeOption $option)
    {
        $this->authorizeSuperAdmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'value' => ['required', 'string', 'max:150'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['value'] = $this->normalizeValue($data['value']);
        validator($data, [
            'value' => [Rule::unique('property_attribute_options', 'value')->where('group_id', $option->group_id)->ignore($option->id)],
        ])->validate();
        $data['is_active'] = $request->boolean('is_active');
        $option->update($data);
        return back()->with('success', 'Property attribute option updated.');
    }

    public function storeMapping(Request $request)
    {
        $this->authorizeSuperAdmin();
        $data = $request->validate([
            'group_id' => ['required', 'integer', 'exists:property_attribute_groups,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
        PropertyAttributeCategoryMap::updateOrCreate(
            ['group_id' => $data['group_id'], 'category_id' => $data['category_id']],
            ['is_required' => $request->boolean('is_required'), 'sort_order' => $data['sort_order']]
        );
        return back()->with('success', 'Category mapping saved.');
    }

    public function updateMapping(Request $request, PropertyAttributeCategoryMap $mapping)
    {
        $this->authorizeSuperAdmin();
        $data = $request->validate([
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
        $mapping->update(['is_required' => $request->boolean('is_required'), 'sort_order' => $data['sort_order']]);
        return back()->with('success', 'Category mapping updated.');
    }

    public function destroyMapping(PropertyAttributeCategoryMap $mapping)
    {
        $this->authorizeSuperAdmin();
        $mapping->delete();
        return back()->with('success', 'Category mapping removed.');
    }

    private function normalizeValue(string $value): string
    {
        return Str::of($value)->lower()->replace('+', ' plus ')->slug('_')->toString();
    }
}
