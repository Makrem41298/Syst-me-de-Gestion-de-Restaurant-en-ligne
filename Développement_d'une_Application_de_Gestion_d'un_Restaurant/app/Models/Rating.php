<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Rating extends Pivot
{
    use HasFactory;
    protected $guarded=[];
    protected $table = 'ratingables';
    public $timestamps = true;


}
