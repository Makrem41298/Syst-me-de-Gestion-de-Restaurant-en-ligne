<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function contracts():HasMany
    {
        return $this->hasMany(Contract::class);
    }

}
