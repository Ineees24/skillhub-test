<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\AuthServiceClient;

class AuthController extends Controller
{
    public function __construct(
        private AuthServiceClient $authServiceClient
    ) {}

    // POST /api/login
    public function login(Request $request)
    {
        // Login SSO: le client calcule (nonce,timestamp,hmac) et on délègue à auth-service Spring.
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'nonce' => 'required|string|min:16|max:120',
            'timestamp' => 'required|integer',
            'hmac' => 'required|string|size:64',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $resp = $this->authServiceClient->login($request->only(['email', 'nonce', 'timestamp', 'hmac']));
        if (!$resp->ok()) {
            return response()->json(['error' => 'Login échoué'], 401);
        }

        $data = $resp->json();
        $email = is_array($data) && isset($data['user']['email']) ? (string) $data['user']['email'] : (string) $request->email;
        $user = User::query()->where('email', $email)->first();

        return response()->json([
            'access_token' => $data['accessToken'] ?? null,
            'token_type' => $data['tokenType'] ?? 'bearer',
            'expires_at' => $data['expiresAt'] ?? null,
            'user' => $user,
        ]);
    }

    // POST /api/register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:APPRENANT,FORMATEUR,ADMINISTRATEUR',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nom'      => $request->nom,
            'prenom'   => $request->prenom,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);
        // On enregistre aussi dans auth-service (mot de passe chiffré Master Key).
        try {
            $authResp = $this->authServiceClient->register([
                'email' => $request->email,
                'role' => $request->role,
                'password' => $request->password,
            ]);
        } catch (\Throwable $e) {
            $user->delete();
            return response()->json(['error' => 'Auth-service unreachable'], 502);
        }

        if (!$authResp->successful()) {
            // rollback local si auth-service refuse
            $user->delete();
            return response()->json(['error' => 'Auth-service register failed'], 502);
        }

        return response()->json([
            'user'         => $user,
        ], 201);
    }

    // GET /api/me
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if (is_string($token) && $token !== '') {
            $this->authServiceClient->logout($token);
        }
        return response()->json(['message' => 'Déconnexion réussie']);
    }
}