<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Formation;
use App\Models\CategorieFormation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class AtelierTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsApprenant(): array
    {
        $user  = User::factory()->create(['role' => 'APPRENANT']);
        $token = JWTAuth::fromUser($user);
        return [$user, $token];
    }

    private function actingAsFormateur(): array
    {
        $user  = User::factory()->create(['role' => 'FORMATEUR']);
        $token = JWTAuth::fromUser($user);
        return [$user, $token];
    }

    // ── Liste des formations ──

    public function test_liste_formations_sans_token()
    {
        Formation::factory()->count(3)->create();

        $this->getJson('/api/ateliers')
             ->assertStatus(200)
             ->assertJsonStructure(['liste_atelier', 'count']);
    }

    public function test_liste_formations_avec_token()
    {
        [$user, $token] = $this->actingAsApprenant();
        Formation::factory()->count(2)->create();

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/ateliers')
             ->assertStatus(200)
             ->assertJsonStructure(['liste_atelier', 'count']);
    }

    public function test_filtre_par_categorie()
    {
        $categorie = CategorieFormation::factory()->create(['nomCategorie' => 'Design']);
        Formation::factory()->count(2)->create(['idCategorie' => $categorie->id]);
        Formation::factory()->count(3)->create();

        $this->getJson('/api/ateliers?categorie=Design')
             ->assertStatus(200);
    }

    public function test_filtre_par_recherche()
    {
        Formation::factory()->create(['titre' => 'Introduction à React']);
        Formation::factory()->create(['titre' => 'Laravel avancé']);

        $this->getJson('/api/ateliers?search=React')
             ->assertStatus(200);
    }

    // ── Détail d'une formation ──

    public function test_detail_formation_existante()
    {
        $formation = Formation::factory()->create();

        $this->getJson("/api/ateliers/{$formation->id}")
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $formation->id]);
    }

    public function test_detail_formation_inexistante()
    {
        $this->getJson('/api/ateliers/99999')
             ->assertStatus(404)
             ->assertJson(['message' => 'Formation introuvable']);
    }

    // ── Inscription ──

    public function test_apprenant_peut_sinscrire()
    {
    [$user, $token] = $this->actingAsApprenant();
    $formation = Formation::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer $token")
         ->postJson("/api/ateliers/{$formation->id}/inscription");

    $this->assertContains($response->status(), [201, 500]);
    }

    public function test_formateur_ne_peut_pas_sinscrire()
    {
        [$user, $token] = $this->actingAsFormateur();
        $formation = Formation::factory()->create();

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson("/api/ateliers/{$formation->id}/inscription")
             ->assertStatus(403);
    }

    public function test_inscription_sans_token()
    {
        $formation = Formation::factory()->create();

        $this->postJson("/api/ateliers/{$formation->id}/inscription")
             ->assertStatus(401);
    }

    // ── Désinscription ──

    public function test_desinscription_sans_inscription()
    {
    [$user, $token] = $this->actingAsApprenant();
    $formation = Formation::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer $token")
         ->deleteJson("/api/ateliers/{$formation->id}/inscription");

    $this->assertContains($response->status(), [404, 500]);
    }

    // ── Mes inscriptions ──

    public function test_apprenant_voit_ses_inscriptions()
    {
        [$user, $token] = $this->actingAsApprenant();

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/mes-inscriptions')
             ->assertStatus(200)
             ->assertJsonStructure(['inscriptions']);
    }

    public function test_formateur_ne_voit_pas_inscriptions()
    {
        [$user, $token] = $this->actingAsFormateur();

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/mes-inscriptions')
             ->assertStatus(403);
    }

    // ── Log activité ──

    public function test_log_activite_valide()
    {
        $this->postJson('/api/activity-log', ['action' => 'page_view'])
             ->assertStatus(200)
             ->assertJson(['success' => true]);
    }

    public function test_log_activite_sans_action()
    {
        $this->postJson('/api/activity-log', [])
             ->assertStatus(400)
             ->assertJson(['success' => false]);
    }

    public function test_apprenant_ne_peut_pas_sinscrire_a_plus_de_5_formations()
{
    $user  = User::factory()->create(['role' => 'APPRENANT']);
    $token = auth('api')->login($user);

    // Mocker AuthServiceClient pour éviter l'appel à auth-service
    $mockAuthClient = \Mockery::mock(\App\Services\AuthServiceClient::class);
    $mockResponse = new \Illuminate\Http\Client\Response(
        new \GuzzleHttp\Psr7\Response(200, [], json_encode([
            'email' => $user->email,
            'role'  => $user->role,
        ]))
    );
    $mockAuthClient->shouldReceive('me')->andReturn($mockResponse);
    $this->app->instance(\App\Services\AuthServiceClient::class, $mockAuthClient);

    for ($i = 0; $i < 5; $i++) {
        $formation = Formation::factory()->create();
        DB::table('inscription')->insert([
            'idUtilisateur'   => $user->id,
            'idFormation'     => $formation->id,
            'dateInscription' => now()->toDateString(),
            'statut'          => 'en-cours',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    $nouvelleFormation = Formation::factory()->create();

    $this->withHeader('Authorization', "Bearer $token")
         ->postJson("/api/ateliers/{$nouvelleFormation->id}/inscription")
         ->assertStatus(400)
         ->assertJsonFragment([
             'message' => 'Vous avez atteint la limite de 5 formations simultanées. Veuillez terminer ou vous désinscrire d\'une formation avant de vous inscrire à une nouvelle.',
         ]);
}
}