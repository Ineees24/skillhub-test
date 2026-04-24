<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthServiceClient
{
    private function client(): PendingRequest
    {
        // Prefer the container env var at runtime (works even if config is cached).
        // Fallback: in Docker, "auth-service" is reachable via the compose network.
        $fallback = file_exists('/.dockerenv') ? 'http://auth-service:8081' : 'http://localhost:8081';
        $envUrl = getenv('AUTH_SERVICE_URL') ?: null;
        $cfgUrl = config('services.auth_service.url');
        $baseUrl = (string) ($envUrl ?: $cfgUrl ?: $fallback);
        $inDocker = file_exists('/.dockerenv');

        // If running Laravel locally (no Docker), "auth-service" hostname is unreachable.
        if (!$inDocker && str_contains($baseUrl, 'auth-service')) {
            $baseUrl = 'http://localhost:8081';
        }

        Log::info('AuthServiceClient baseUrl', [
            'baseUrl' => $baseUrl,
            'env' => $envUrl,
            'config' => $cfgUrl,
            'inDocker' => $inDocker,
        ]);

        return Http::baseUrl(rtrim($baseUrl, '/'))
            ->acceptJson()
            ->timeout(10);
    }

    public function register(array $payload)
    {
        return $this->client()->post('/api/auth/register', $payload);
    }

    public function login(array $payload)
    {
        return $this->client()->post('/api/auth/login', $payload);
    }

    public function introspect(string $token)
    {
        return $this->client()->post('/api/auth/introspect', ['token' => $token]);
    }

    public function me(string $token)
    {
        return $this->client()
            ->withToken($token)
            ->get('/api/auth/me');
    }

    public function logout(string $token)
    {
        return $this->client()
            ->withToken($token)
            ->post('/api/auth/logout');
    }
}

