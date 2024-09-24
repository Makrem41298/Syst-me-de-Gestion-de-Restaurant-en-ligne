<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Service extends Model
{
    use HasFactory;
    protected $guarded=[];



    public function users():MorphToMany
    {
        return  $this->morphToMany(User::class, 'ratingable','ratingables')->withPivot('id');
    }
}
