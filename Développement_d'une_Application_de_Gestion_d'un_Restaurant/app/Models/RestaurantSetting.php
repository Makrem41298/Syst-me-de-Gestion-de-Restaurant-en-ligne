<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    use HasFactory;
    protected $table='restaurant_settings';
    protected $guarded=['created_at','updated_at'];
    protected $casts=[
        'opening_hours'=>'object'
        ];
}
