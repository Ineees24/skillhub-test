<?php

namespace Tests\Feature;

use App\Models\Formation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApprenantListeTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsFormateur(): array
    {
        $user  = User::factory()->create(['role' => 'FORMATEUR']);
        $token = JWTAuth::fromUser($user);
        return [$user, $token];
    }

    private function actingAsApprenant(): array
    {
        $user  = User::factory()->create(['role' => 'APPRENANT']);
        $token = JWTAuth::fromUser($user);
        return [$user, $token];
    }

    private function inscrireApprenant(int $userId, int $formationId): void
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

    public function test_formateur_proprietaire_voit_apprenants(): void
    {
        [$formateur, $token] = $this->actingAsFormateur();
        $formation = Formation::factory()->create(['idUtilisateur' => $formateur->id]);
        [$apprenant] = $this->actingAsApprenant();
        $this->inscrireApprenant($apprenant->id, $formation->id);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson("/api/formations/{$formation->id}/apprenants")
             ->assertStatus(200)
             ->assertJsonStructure([
                 'apprenants' => [
                     '*' => ['id', 'nom', 'email', 'progression', 'date_inscription']
                 ]
             ]);
    }

    public function test_formateur_non_proprietaire_retourne_403(): void
    {
        [$formateur1, $token1] = $this->actingAsFormateur();
        [$formateur2, $token2] = $this->actingAsFormateur();
        $formation = Formation::factory()->create(['idUtilisateur' => $formateur1->id]);

        $this->withHeader('Authorization', "Bearer $token2")
             ->getJson("/api/formations/{$formation->id}/apprenants")
             ->assertStatus(403);
    }

    public function test_formation_sans_apprenant_retourne_tableau_vide(): void
    {
        [$formateur, $token] = $this->actingAsFormateur();
        $formation = Formation::factory()->create(['idUtilisateur' => $formateur->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson("/api/formations/{$formation->id}/apprenants")
             ->assertStatus(200)
             ->assertJson(['apprenants' => []]);
    }

    public function test_sans_token_retourne_401(): void
    {
        $formation = Formation::factory()->create();

        $this->getJson("/api/formations/{$formation->id}/apprenants")
             ->assertStatus(401);
    }

    public function test_formation_inexistante_retourne_404(): void
    {
        [$formateur, $token] = $this->actingAsFormateur();

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson("/api/formations/99999/apprenants")
             ->assertStatus(404);
    }
}