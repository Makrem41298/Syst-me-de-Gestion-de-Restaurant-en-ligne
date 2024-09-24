<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;


class Booking extends Model
{
    use HasFactory;
   protected $guarded=[];
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);

    }
    public  function table(): BelongsTo
    {
        return $this->belongsTo(Table::class)->withDefault()->select('id');

    }

}
