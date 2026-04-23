<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_avec_donnees_valides()
    {
        $response = $this->postJson('/api/register', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'email'    => 'jean@test.com',
            'password' => 'password123',
            'role'     => 'FORMATEUR',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['access_token', 'token_type', 'user']);

        $this->assertDatabaseHas('users', ['email' => 'jean@test.com']);
    }

    public function test_register_echoue_champs_manquants()
    {
        $response = $this->postJson('/api/register', ['email' => 'jean@test.com']);
        $response->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_register_echoue_email_duplique()
    {
        User::factory()->create(['email' => 'jean@test.com']);

        $response = $this->postJson('/api/register', [
            'nom'      => 'Autre',
            'prenom'   => 'Personne',
            'email'    => 'jean@test.com',
            'password' => 'password123',
            'role'     => 'APPRENANT',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_avec_bons_identifiants()
    {
        User::factory()->create([
            'email'    => 'jean@test.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'jean@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user']);
    }

    public function test_login_echoue_mauvais_mot_de_passe()
    {
        User::factory()->create([
            'email'    => 'jean@test.com',
            'password' => Hash::make('password123'),
        ]);

        $this->postJson('/api/login', [
            'email'    => 'jean@test.com',
            'password' => 'mauvais',
        ])->assertStatus(401);
    }

    public function test_acces_profil_avec_token()
    {
        $user  = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/me')
             ->assertStatus(200)
             ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_acces_profil_sans_token()
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_logout_avec_token()
    {
        $user  = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/logout')
             ->assertStatus(200)
             ->assertJson(['message' => 'Déconnexion réussie']);
    }
}