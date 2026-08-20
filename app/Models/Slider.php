<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table    = "sliders";
    protected $fillable = ["type","image","web_image","link","sequence","category_id","propertys_id","show_property_details","default_data"];
}
