<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Order extends Model
{
    use HasFactory;


    protected $guarded=[];


    public function items():BelongsToMany
    {
        return $this->belongsToMany(Item::class,'order_line')->using(OrderLine::class)->withPivot(['quantity','unit_price']);

    }
    public function payment():HasOne
    {
        return $this->hasOne(Payment::class);

    }

    public function delivery():HasOne
    {
        return $this->hasOne(Delivery::class);

    }
    public function orderable():MorphTo
    {
        return $this->morphTo();

    }
    public function admin():BelongsTo
    {
       return $this->belongsTo(Admin::class);

    }

}
