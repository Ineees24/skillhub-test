<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_retourne_token_valide(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@test.com',
            'password' => bcrypt('secret123'),
            'role'     => 'APPRENANT',
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@test.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user']);
    }

    public function test_login_echoue_mauvais_mot_de_passe(): void
    {
        User::factory()->create([
            'email'    => 'test@test.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->postJson('/api/login', [
            'email'    => 'test@test.com',
            'password' => 'mauvais',
        ])->assertStatus(401);
    }

    public function test_login_echoue_email_inexistant(): void
    {
        $this->postJson('/api/login', [
            'email'    => 'nope@test.com',
            'password' => 'secret123',
        ])->assertStatus(401);
    }

    public function test_register_retourne_token(): void
    {
        $this->postJson('/api/register', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'email'    => 'jean@test.com',
            'password' => 'secret123',
            'role'     => 'APPRENANT',
        ])->assertStatus(201)
          ->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_register_echoue_role_invalide(): void
    {
        $this->postJson('/api/register', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'email'    => 'jean@test.com',
            'password' => 'secret123',
            'role'     => 'SUPERADMIN',
        ])->assertStatus(422);
    }

    public function test_register_echoue_password_trop_court(): void
    {
        $this->postJson('/api/register', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'email'    => 'jean@test.com',
            'password' => '123',
            'role'     => 'APPRENANT',
        ])->assertStatus(422);
    }

    public function test_register_echoue_champs_manquants(): void
    {
        $this->postJson('/api/register', [])
             ->assertStatus(422)
             ->assertJsonStructure(['errors']);
    }

    public function test_me_retourne_utilisateur_connecte(): void
    {
        $user  = User::factory()->create(['role' => 'APPRENANT']);
        $token = auth('api')->login($user);

        $this->withToken($token)->getJson('/api/me')
             ->assertStatus(200)
             ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_me_sans_token_renvoie_401(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_logout_invalide_le_token(): void
    {
        $user  = User::factory()->create(['role' => 'APPRENANT']);
        $token = auth('api')->login($user);

        $this->withToken($token)->postJson('/api/logout')
             ->assertStatus(200);
    }
}