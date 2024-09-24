<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Table extends Model
{
    use HasFactory;
    protected $guarded=[];



    public function orders():MorphMany
    {
    return $this->MorphMany(Order::class,'orderable');
    }
    public function bookings():HasMany
    {
        return $this->hasMany(Booking::class);

    }
}
