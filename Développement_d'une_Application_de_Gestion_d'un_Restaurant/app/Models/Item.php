<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Item extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function SubCategory():BelongsTo

    {
    return $this->belongsTo(SubCategory::class)->withDefault();
    }
    public function Order():BelongsToMany
    {
        return $this->belongsToMany(Order::class,'order_line')->using(OrderLine::class);

    }


    public function users():MorphToMany
    {
        return $this->morphToMany(User::class, 'ratingable','ratingables')->withPivot('comment')->withTimestamps()->select('name');

    }
    public function images():HasMany
    {
        return $this->hasMany(Image::class);
    }

}
