<?php

namespace App\Http\Middleware;

use App\Auth\SpringUser;
use App\Models\User;
use App\Services\AuthServiceClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SpringAuthenticate
{
    public function __construct(
        private AuthServiceClient $authServiceClient
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!is_string($token) || $token === '') {
            return response()->json(['error' => 'Token absent'], 401);
        }

        $me = $this->authServiceClient->me($token);
        if (!$me->ok()) {
            return response()->json(['error' => 'Token invalide ou expiré'], 401);
        }

        $meJson = $me->json();
        $email = is_array($meJson) ? ($meJson['email'] ?? null) : null;
        $role = is_array($meJson) ? ($meJson['role'] ?? null) : null;

        if (!is_string($email) || !is_string($role)) {
            return response()->json(['error' => 'Réponse auth-service invalide'], 500);
        }

        // On mappe l'identité Spring -> User local Skillhub (source des ids utilisés par le métier).
        $localUser = User::query()->where('email', $email)->first();
        if (!$localUser) {
            return response()->json(['error' => 'Utilisateur introuvable dans skillhub'], 401);
        }

        $springUser = new SpringUser((int) $localUser->id, $email, $role);
        $request->setUserResolver(static fn () => $springUser);

        return $next($request);
    }
}

