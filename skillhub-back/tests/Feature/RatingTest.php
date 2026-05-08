<?php

namespace Tests\Feature;

use App\Models\Formation;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsApprenant(): array
    {
        $user  = User::factory()->create(['role' => 'APPRENANT']);
        $token = JWTAuth::fromUser($user);
        return [$user, $token];
    }

    private function inscrireUtilisateur(int $userId, int $formationId): void
    {
        DB::table('inscription')->insert([
            'idUtilisateur'   => $userId,
            'idFormation'     => $formationId,
            'dateInscription' => now()->toDateString(),
            'statut'          => 'en-cours',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function test_apprenant_inscrit_peut_noter(): void
    {
        [$user, $token] = $this->actingAsApprenant();
        $formation = Formation::factory()->create();
        $this->inscrireUtilisateur($user->id, $formation->id);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson("/api/formations/{$formation->id}/noter", [
                 'note'        => 4,
                 'commentaire' => 'Très bonne formation',
             ])
             ->assertStatus(201);

        $this->assertDatabaseHas('rating', [
            'idUtilisateur' => $user->id,
            'idFormation'   => $formation->id,
            'note'          => 4,
        ]);
    }

    public function test_apprenant_ne_peut_pas_noter_deux_fois(): void
    {
        [$user, $token] = $this->actingAsApprenant();
        $formation = Formation::factory()->create();
        $this->inscrireUtilisateur($user->id, $formation->id);

        Rating::create([
            'idUtilisateur' => $user->id,
            'idFormation'   => $formation->id,
            'note'          => 3,
            'commentaire'   => 'Bien',
        ]);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson("/api/formations/{$formation->id}/noter", [
                 'note' => 5,
             ])
             ->assertStatus(400);
    }

    public function test_note_hors_intervalle_retourne_400(): void
    {
        [$user, $token] = $this->actingAsApprenant();
        $formation = Formation::factory()->create();
        $this->inscrireUtilisateur($user->id, $formation->id);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson("/api/formations/{$formation->id}/noter", [
                 'note' => 6,
             ])
             ->assertStatus(422);
    }

    public function test_apprenant_non_inscrit_retourne_403(): void
    {
        [$user, $token] = $this->actingAsApprenant();
        $formation = Formation::factory()->create();

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson("/api/formations/{$formation->id}/noter", [
                 'note' => 4,
             ])
             ->assertStatus(403);
    }

    public function test_sans_token_retourne_401(): void
    {
        $formation = Formation::factory()->create();

        $this->postJson("/api/formations/{$formation->id}/noter", [
            'note' => 4,
        ])->assertStatus(401);
    }
}