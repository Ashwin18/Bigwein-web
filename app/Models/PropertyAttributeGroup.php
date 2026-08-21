<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAttributeGroup extends Model
{
    protected $fillable = ['name', 'code', 'input_type', 'scope', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function options()
    {
        return $this->hasMany(PropertyAttributeOption::class, 'group_id')->orderBy('sort_order')->orderBy('id');
    }

    public function categoryMappings()
    {
        return $this->hasMany(PropertyAttributeCategoryMap::class, 'group_id')->orderBy('sort_order')->orderBy('id');
    }

    public function values()
    {
        return $this->hasMany(PropertyAttributeValue::class, 'group_id');
    }
}
