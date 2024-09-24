<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasFactory;
    protected  $guarded=[];

    public function path():Attribute
    {
        return  Attribute::make(
            get: fn($path) => $path?"/storage/".$path:null
        );

    }
    public function item():BelongsTo
    {
        return $this->belongsTo(Item::class)->withDefault();

    }
}
