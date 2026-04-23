<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_user()
    {
        $user = User::factory()->create(['role' => 'FORMATEUR']);

        $this->assertDatabaseHas('users', ['email' => $user->email]);
        $this->assertEquals('FORMATEUR', $user->role);
    }

    public function test_password_cache_dans_json()
    {
        $user = User::factory()->create();
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_jwt_identifier()
    {
        $user = User::factory()->create();
        $this->assertEquals($user->id, $user->getJWTIdentifier());
    }

    public function test_role_dans_jwt_claims()
    {
        $user   = User::factory()->create(['role' => 'FORMATEUR']);
        $claims = $user->getJWTCustomClaims();

        $this->assertArrayHasKey('role', $claims);
        $this->assertEquals('FORMATEUR', $claims['role']);
    }
}