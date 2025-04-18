<?php

namespace App\Models;

use Doctrine\Inflector\WordInflector;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'representent_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'avoirs', 'user_id', 'role_id');
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class, 'users');
    }

    public function representant()
    {
        return $this->belongsTo(Representant::class, 'representent_id');
    }

    public function prix()
    {
        return $this->hasMany(Prix::class, 'users');
    }

    function _zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, "user_id");
    }
}
