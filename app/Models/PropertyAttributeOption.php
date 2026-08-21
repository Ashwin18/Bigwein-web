<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAttributeOption extends Model
{
    protected $fillable = ['group_id', 'name', 'value', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function group()
    {
        return $this->belongsTo(PropertyAttributeGroup::class, 'group_id');
    }

    public function values()
    {
        return $this->hasMany(PropertyAttributeValue::class, 'option_id');
    }
}
