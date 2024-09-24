<?php

namespace App\Models;
 use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
 use Illuminate\Database\Eloquent\Relations\MorphOne;
 use Illuminate\Database\Eloquent\Relations\MorphTo;
 use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
 use Symfony\Component\HttpKernel\Profiler\Profile;
 use Tymon\JWTAuth\Contracts\JWTSubject;




class User extends Authenticatable implements JWTSubject ,MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     *
     */



    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function bookings(): HasMany
    {
            return $this->HasMany(Booking::class);
    }
    public function services():MorphToMany
    {
        return  $this->morphedByMany(Service::class, 'ratingable','ratingables')->withTimestamps();
    }
    public  function items():MorphToMany
    {
        return $this->morphToMany(Item::class, 'ratingable','ratingables')->withTimestamps();
    }
    public function orders():MorphMany
    {
        return $this->MorphMany(Order::class,'orderable');
    }
    public function profile():MorphOne
    {
        return $this->MorphOne(\App\Models\Profile::class,'profileable');

    }




    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
