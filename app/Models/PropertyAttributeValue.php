<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAttributeValue extends Model
{
    protected $fillable = ['property_id', 'group_id', 'option_id', 'value_text'];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function group()
    {
        return $this->belongsTo(PropertyAttributeGroup::class, 'group_id');
    }

    public function option()
    {
        return $this->belongsTo(PropertyAttributeOption::class, 'option_id');
    }
}
