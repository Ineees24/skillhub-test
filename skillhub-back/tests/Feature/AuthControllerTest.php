<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthServiceClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private function mockAuthClient(): \Mockery\MockInterface
    {
        $mock = Mockery::mock(AuthServiceClient::class);
        $this->app->instance(AuthServiceClient::class, $mock);
        return $mock;
    }

    private function fakeResponse(bool $ok, array $data = [], int $status = 200)
    {
        $psrResponse = new \GuzzleHttp\Psr7\Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
        return new Response($psrResponse);
    }

    // ─── LOGIN ───────────────────────────────────────────────

    public function test_login_validation_echoue_champs_manquants(): void
    {
        $this->postJson('/api/login', [])
             ->assertStatus(422)
             ->assertJsonStructure(['errors']);
    }

    public function test_login_validation_echoue_email_invalide(): void
    {
        $this->postJson('/api/login', [
            'email'     => 'pas-un-email',
            'nonce'     => str_repeat('a', 16),
            'timestamp' => time(),
            'hmac'      => str_repeat('a', 64),
        ])->assertStatus(422);
    }

    public function test_login_echoue_si_auth_service_refuse(): void
    {
        $mock = $this->mockAuthClient();
        $mock->shouldReceive('login')
             ->once()
             ->andReturn($this->fakeResponse(false, [], 401));

        $this->postJson('/api/login', [
            'email'     => 'test@test.com',
            'nonce'     => str_repeat('a', 16),
            'timestamp' => time(),
            'hmac'      => str_repeat('a', 64),
        ])->assertStatus(401);
    }

    public function test_login_retourne_token_si_auth_service_ok(): void
    {
        $user = User::factory()->create(['email' => 'test@test.com']);

        $mock = $this->mockAuthClient();
        $mock->shouldReceive('login')
             ->once()
             ->andReturn($this->fakeResponse(true, [
                 'accessToken' => 'fake-token',
                 'tokenType'   => 'bearer',
                 'expiresAt'   => '2099-01-01',
                 'user'        => ['email' => 'test@test.com'],
             ]));

        $this->postJson('/api/login', [
            'email'     => 'test@test.com',
            'nonce'     => str_repeat('a', 16),
            'timestamp' => time(),
            'hmac'      => str_repeat('a', 64),
        ])->assertStatus(200)
          ->assertJsonStructure(['access_token', 'token_type', 'expires_at', 'user']);
    }

    // ─── REGISTER ────────────────────────────────────────────

    public function test_register_validation_echoue_champs_manquants(): void
    {
        $this->postJson('/api/register', [])
             ->assertStatus(422)
             ->assertJsonStructure(['errors']);
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

    public function test_register_echoue_email_duplique(): void
    {
        User::factory()->create(['email' => 'jean@test.com']);

        $this->postJson('/api/register', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'email'    => 'jean@test.com',
            'password' => 'secret123',
            'role'     => 'APPRENANT',
        ])->assertStatus(422);
    }

    public function test_register_echoue_si_auth_service_unreachable(): void
    {
        $mock = $this->mockAuthClient();
        $mock->shouldReceive('register')
             ->once()
             ->andThrow(new \Exception('unreachable'));

        $this->postJson('/api/register', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'email'    => 'jean@test.com',
            'password' => 'secret123',
            'role'     => 'APPRENANT',
        ])->assertStatus(502);

        $this->assertDatabaseMissing('users', ['email' => 'jean@test.com']);
    }

    public function test_register_echoue_si_auth_service_refuse(): void
    {
        $mock = $this->mockAuthClient();
        $mock->shouldReceive('register')
             ->once()
             ->andReturn($this->fakeResponse(false, [], 400));

        $this->postJson('/api/register', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'email'    => 'jean@test.com',
            'password' => 'secret123',
            'role'     => 'APPRENANT',
        ])->assertStatus(502);

        $this->assertDatabaseMissing('users', ['email' => 'jean@test.com']);
    }

    public function test_register_cree_utilisateur_si_tout_ok(): void
    {
        $mock = $this->mockAuthClient();
        $mock->shouldReceive('register')
             ->once()
             ->andReturn($this->fakeResponse(true, [], 200));

        $this->postJson('/api/register', [
            'nom'      => 'Dupont',
            'prenom'   => 'Jean',
            'email'    => 'jean@test.com',
            'password' => 'secret123',
            'role'     => 'APPRENANT',
        ])->assertStatus(201)
          ->assertJsonStructure(['user']);

        $this->assertDatabaseHas('users', ['email' => 'jean@test.com']);
    }

    // ─── ME ──────────────────────────────────────────────────

    public function test_me_sans_token_renvoie_401(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_retourne_utilisateur_connecte(): void
    {
        $user  = User::factory()->create(['role' => 'APPRENANT']);
        $token = auth('api')->login($user);

        $this->withToken($token)->getJson('/api/me')
             ->assertStatus(200)
             ->assertJsonFragment(['email' => $user->email]);
    }

    // ─── LOGOUT ──────────────────────────────────────────────

    public function test_logout_avec_token(): void
    {
        $user  = User::factory()->create(['role' => 'APPRENANT']);
        $token = auth('api')->login($user);

        $mock = $this->mockAuthClient();
        $mock->shouldReceive('logout')->once();

        $this->withToken($token)->postJson('/api/logout')
             ->assertStatus(200)
             ->assertJson(['message' => 'Déconnexion réussie']);
    }

    
    public function test_logout_sans_token(): void
{
    $this->postJson('/api/logout')
         ->assertStatus(401);
}
}