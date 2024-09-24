<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderLine extends Pivot
{
    protected $table = 'order_line';
    public $timestamps = false;

}
