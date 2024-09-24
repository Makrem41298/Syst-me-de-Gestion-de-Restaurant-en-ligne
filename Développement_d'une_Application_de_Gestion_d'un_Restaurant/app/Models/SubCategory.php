<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nette\Utils\RegexpException;

class SubCategory extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function category():BelongsTo
    {
    return $this->belongsTo(Category::class)->withDefault();
    }
    public function item():HasMany
    {
        return $this->hasMany(Item::class);
    }
}
