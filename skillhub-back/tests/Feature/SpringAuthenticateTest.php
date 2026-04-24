<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthServiceClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SpringAuthenticateTest extends TestCase
{
    use RefreshDatabase;

    private function mockAuthClient(): \Mockery\MockInterface
    {
        $mock = Mockery::mock(AuthServiceClient::class);
        $this->app->instance(AuthServiceClient::class, $mock);
        return $mock;
    }

    private function fakeResponse(int $status, array $data = []): Response
    {
        return new Response(new GuzzleResponse(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        ));
    }

    public function test_sans_token_retourne_401(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_avec_jwt_valide_en_testing(): void
    {
        $user = User::factory()->create(['role' => 'APPRENANT']);
        $token = JWTAuth::fromUser($user);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/me')
             ->assertStatus(200)
             ->assertJsonFragment(['email' => $user->email]);
    }
}