<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class SpringUser implements Authenticatable
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $role,
    ) {}

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        
    }

    public function getRememberTokenName()
    {
        return null;
    }
}

