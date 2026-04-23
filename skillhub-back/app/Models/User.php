<?php

namespace App\Models;

// Interface obligatoire pour utiliser JWT avec tymon/jwt-auth
use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Le modèle User hérite de Authenticatable
// et implémente JWTSubject (obligatoire pour JWT)
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role',
        'nom',
        'prenom',
        'email',
        'password',
        'niveauEtude',
        'certification',
        'derniereConnexion',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,  // on met le rôle dans le token
        ];
    }
}