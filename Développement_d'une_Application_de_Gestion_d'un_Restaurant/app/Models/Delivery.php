<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;
    protected $guarded=[];


    public function order():BelongsTo
    {
        return $this->belongsTo(Order::class);

    }

    public function contract():BelongsTo
    {
        return $this->belongsTo(Contract::class)->withDefault();

    }


}
