@extends('layouts.main')

@section('title', 'Property Attribute Masters')

@section('page-title')
<div class="page-title">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4><i class="bi bi-ui-checks-grid me-2 text-danger"></i>Property Attribute Masters</h4>
            <p class="text-muted mb-0">Manage normalized property groups, options and category mappings.</p>
        </div>
    </div>
</div>
@endsection

@section('content')
<section class="section">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="alert alert-info py-2">
        These masters are infrastructure-only in Phase 1. Current posting and Search Settings behavior remains unchanged.
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Add Attribute Group</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('property-attributes.groups.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-3"><label class="form-label">Name</label><input name="name" class="form-control" required maxlength="150"></div>
                <div class="col-md-2"><label class="form-label">Code</label><input name="code" class="form-control" required pattern="[a-z0-9_]+" placeholder="example_type"></div>
                <div class="col-md-2"><label class="form-label">Input Type</label><select name="input_type" class="form-select"><option value="single_select">Single Select</option></select></div>
                <div class="col-md-2"><label class="form-label">Scope</label><select name="scope" class="form-select"><option value="category">Category</option><option value="global">Global</option></select></div>
                <div class="col-md-1"><label class="form-label">Order</label><input name="sort_order" type="number" min="0" value="0" class="form-control" required></div>
                <div class="col-md-1"><div class="form-check mb-2"><input type="hidden" name="is_active" value="0"><input name="is_active" value="1" type="checkbox" class="form-check-input" checked id="new-group-active"><label class="form-check-label" for="new-group-active">Active</label></div></div>
                <div class="col-md-1"><button class="btn btn-danger w-100">Add</button></div>
            </form>
        </div>
    </div>

    @forelse($groups as $group)
    <div class="card mb-4 border">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div><strong>{{ $group->name }}</strong> <code>{{ $group->code }}</code></div>
            <span class="badge {{ $group->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('property-attributes.groups.update', $group) }}" class="row g-2 align-items-end mb-4">
                @csrf @method('PUT')
                <div class="col-md-3"><label class="form-label">Name</label><input name="name" value="{{ $group->name }}" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Code</label><input name="code" value="{{ $group->code }}" class="form-control" required pattern="[a-z0-9_]+"></div>
                <div class="col-md-2"><label class="form-label">Input Type</label><select name="input_type" class="form-select"><option value="single_select" selected>Single Select</option></select></div>
                <div class="col-md-2"><label class="form-label">Scope</label><select name="scope" class="form-select"><option value="category" @selected($group->scope === 'category')>Category</option><option value="global" @selected($group->scope === 'global')>Global</option></select></div>
                <div class="col-md-1"><label class="form-label">Order</label><input name="sort_order" type="number" min="0" value="{{ $group->sort_order }}" class="form-control" required></div>
                <div class="col-md-1"><div class="form-check mb-2"><input type="hidden" name="is_active" value="0"><input name="is_active" value="1" type="checkbox" class="form-check-input" @checked($group->is_active) id="group-active-{{ $group->id }}"><label class="form-check-label" for="group-active-{{ $group->id }}">Active</label></div></div>
                <div class="col-md-1"><button class="btn btn-outline-danger w-100">Save</button></div>
            </form>

            <div class="row g-4">
                <div class="col-lg-7">
                    <h6>Options</h6>
                    @foreach($group->options as $option)
                    <form method="POST" action="{{ route('property-attributes.options.update', $option) }}" class="row g-2 align-items-center border rounded p-2 mb-2 bg-light">
                        @csrf @method('PUT')
                        <div class="col-md-4"><input name="name" value="{{ $option->name }}" class="form-control form-control-sm" required aria-label="Option name"></div>
                        <div class="col-md-3"><input name="value" value="{{ $option->value }}" class="form-control form-control-sm" required aria-label="Option value"></div>
                        <div class="col-md-2"><input name="sort_order" type="number" min="0" value="{{ $option->sort_order }}" class="form-control form-control-sm" required aria-label="Sort order"></div>
                        <div class="col-md-2"><div class="form-check"><input type="hidden" name="is_active" value="0"><input name="is_active" value="1" type="checkbox" class="form-check-input" @checked($option->is_active) id="option-active-{{ $option->id }}"><label class="form-check-label" for="option-active-{{ $option->id }}">Active</label></div></div>
                        <div class="col-md-1"><button class="btn btn-sm btn-outline-primary">Save</button></div>
                    </form>
                    @endforeach
                    <form method="POST" action="{{ route('property-attributes.options.store') }}" class="row g-2 align-items-end border border-dashed rounded p-2 mt-3">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $group->id }}">
                        <div class="col-md-4"><label class="form-label small">New option</label><input name="name" class="form-control form-control-sm" required></div>
                        <div class="col-md-3"><label class="form-label small">Value (optional)</label><input name="value" class="form-control form-control-sm" placeholder="auto-generated"></div>
                        <div class="col-md-2"><label class="form-label small">Order</label><input name="sort_order" type="number" min="0" value="0" class="form-control form-control-sm" required></div>
                        <div class="col-md-2"><div class="form-check mb-1"><input type="hidden" name="is_active" value="0"><input name="is_active" value="1" type="checkbox" class="form-check-input" checked id="new-option-active-{{ $group->id }}"><label class="form-check-label" for="new-option-active-{{ $group->id }}">Active</label></div></div>
                        <div class="col-md-1"><button class="btn btn-sm btn-danger">Add</button></div>
                    </form>
                </div>

                <div class="col-lg-5">
                    <h6>Category Mapping</h6>
                    @foreach($group->categoryMappings as $mapping)
                    <div class="d-flex gap-2 align-items-center border rounded p-2 mb-2">
                        <form method="POST" action="{{ route('property-attributes.mappings.update', $mapping) }}" class="d-flex gap-2 align-items-center flex-grow-1">
                            @csrf @method('PUT')
                            <span class="flex-grow-1 small fw-semibold">{{ $mapping->category->category ?? 'Missing category' }}</span>
                            <input name="sort_order" type="number" min="0" value="{{ $mapping->sort_order }}" class="form-control form-control-sm" style="width:75px" aria-label="Mapping order">
                            <div class="form-check"><input type="hidden" name="is_required" value="0"><input name="is_required" value="1" type="checkbox" class="form-check-input" @checked($mapping->is_required) id="mapping-required-{{ $mapping->id }}"><label class="form-check-label small" for="mapping-required-{{ $mapping->id }}">Required</label></div>
                            <button class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                        <form method="POST" action="{{ route('property-attributes.mappings.destroy', $mapping) }}" onsubmit="return confirm('Remove this category mapping?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Remove mapping"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                    @endforeach
                    <form method="POST" action="{{ route('property-attributes.mappings.store') }}" class="row g-2 align-items-end border rounded p-2 mt-3">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $group->id }}">
                        <div class="col-6"><label class="form-label small">Category</label><select name="category_id" class="form-select form-select-sm" required><option value="">Select</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->category }}</option>@endforeach</select></div>
                        <div class="col-2"><label class="form-label small">Order</label><input name="sort_order" type="number" min="0" value="0" class="form-control form-control-sm" required></div>
                        <div class="col-2"><div class="form-check mb-1"><input type="hidden" name="is_required" value="0"><input name="is_required" value="1" type="checkbox" class="form-check-input" id="new-mapping-required-{{ $group->id }}"><label class="form-check-label small" for="new-mapping-required-{{ $group->id }}">Required</label></div></div>
                        <div class="col-2"><button class="btn btn-sm btn-danger w-100">Map</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
        <div class="alert alert-warning">No attribute groups exist. Run the PropertyAttributeMasterSeeder or add a group above.</div>
    @endforelse
</section>
@endsection
