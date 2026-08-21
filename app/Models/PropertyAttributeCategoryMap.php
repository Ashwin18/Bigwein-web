<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAttributeCategoryMap extends Model
{
    protected $table = 'property_attribute_category_map';
    protected $fillable = ['group_id', 'category_id', 'is_required', 'sort_order'];
    protected $casts = ['is_required' => 'boolean', 'sort_order' => 'integer'];

    public function group()
    {
        return $this->belongsTo(PropertyAttributeGroup::class, 'group_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
