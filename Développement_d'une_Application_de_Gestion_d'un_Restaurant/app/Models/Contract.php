<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;
    protected $guarded=[];


    public function delivery ():HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function driver():BelongsTo
    {
        return $this->belongsTo(driver::class)->withDefault();
    }

}
