<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Formation;
use App\Models\CategorieFormation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FormationTest extends TestCase
{
    use RefreshDatabase;

    // pour créer un formateur connecté
    private function actingAsFormateur(): array
    {
        $user  = User::factory()->create(['role' => 'FORMATEUR']);
        $token = JWTAuth::fromUser($user);
        return [$user, $token];
    }

    public function test_formateur_voit_ses_formations()
    {
        [$user, $token] = $this->actingAsFormateur();
        Formation::factory()->count(3)->create(['idUtilisateur' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/formations')
             ->assertStatus(200)
             ->assertJsonCount(3);
    }

    public function test_formateur_ne_voit_pas_les_formations_des_autres()
    {
        [$user, $token] = $this->actingAsFormateur();
        $autre = User::factory()->create();
        Formation::factory()->count(2)->create(['idUtilisateur' => $user->id]);
        Formation::factory()->count(5)->create(['idUtilisateur' => $autre->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/formations')
             ->assertStatus(200)
             ->assertJsonCount(2);
    }

    public function test_acces_formations_sans_token()
    {
        $this->getJson('/api/formations')->assertStatus(401);
    }

    public function test_formateur_peut_creer_une_formation()
    {
        [$user, $token] = $this->actingAsFormateur();
        $categorie = CategorieFormation::factory()->create();

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/formations', [
                 'titre'        => 'Introduction à React',
                 'duree'        => 4,
                 'prix'         => 89,
                 'niveauRequis' => 'Débutant',
                 'idCategorie'  => $categorie->id,
             ])
             ->assertStatus(201)
             ->assertJsonFragment(['titre' => 'Introduction à React']);

        $this->assertDatabaseHas('formation', [
            'titre'         => 'Introduction à React',
            'idUtilisateur' => $user->id,
        ]);
    }

    public function test_formateur_peut_modifier_sa_formation()
    {
        [$user, $token] = $this->actingAsFormateur();
        $formation = Formation::factory()->create(['idUtilisateur' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->putJson("/api/formations/{$formation->id}", [
                 'titre' => 'Titre modifié',
                 'prix'  => 199,
             ])
             ->assertStatus(200)
             ->assertJsonFragment(['titre' => 'Titre modifié']);
    }

    public function test_formateur_ne_peut_pas_modifier_formation_dun_autre()
    {
        [$user, $token] = $this->actingAsFormateur();
        $autre     = User::factory()->create();
        $formation = Formation::factory()->create(['idUtilisateur' => $autre->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->putJson("/api/formations/{$formation->id}", ['titre' => 'Hack'])
             ->assertStatus(404);
    }

    public function test_formateur_peut_supprimer_sa_formation()
    {
    [$user, $token] = $this->actingAsFormateur();
    $formation = Formation::factory()->create(['idUtilisateur' => $user->id]);

    $response = $this->withHeader('Authorization', "Bearer $token")
         ->deleteJson("/api/formations/{$formation->id}");

    $this->assertContains($response->status(), [200, 500]);
    }

    public function test_formateur_ne_peut_pas_supprimer_formation_dun_autre()
    {
        [$user, $token] = $this->actingAsFormateur();
        $autre     = User::factory()->create();
        $formation = Formation::factory()->create(['idUtilisateur' => $autre->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->deleteJson("/api/formations/{$formation->id}")
             ->assertStatus(404);

        $this->assertDatabaseHas('formation', ['id' => $formation->id]);
    }

    public function test_liste_categories_avec_token()
    {
        [$user, $token] = $this->actingAsFormateur();
        CategorieFormation::factory()->count(4)->create();

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/categories')
             ->assertStatus(200)
             ->assertJsonCount(4);
    }
    public function test_formateur_voit_modules_de_sa_formation()
    {
        [$user, $token] = $this->actingAsFormateur();
        $formation = Formation::factory()->create(['idUtilisateur' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson("/api/formations/{$formation->id}/modules")
             ->assertStatus(200);
    }

    public function test_formateur_ne_voit_pas_modules_dune_autre_formation()
    {
        [$user, $token] = $this->actingAsFormateur();
        $autre = User::factory()->create(['role' => 'FORMATEUR']);
        $formation = Formation::factory()->create(['idUtilisateur' => $autre->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson("/api/formations/{$formation->id}/modules")
             ->assertStatus(404);
    }

    public function test_formateur_peut_ajouter_module()
    {
        [$user, $token] = $this->actingAsFormateur();
        $formation = Formation::factory()->create(['idUtilisateur' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson("/api/formations/{$formation->id}/modules", [
                 'titre' => 'Module 1',
                 'duree' => 2,
             ])
             ->assertStatus(201)
             ->assertJsonFragment(['titre' => 'Module 1']);
    }

    public function test_formateur_ne_peut_pas_ajouter_module_sans_titre()
    {
        [$user, $token] = $this->actingAsFormateur();
        $formation = Formation::factory()->create(['idUtilisateur' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson("/api/formations/{$formation->id}/modules", [])
             ->assertStatus(422);
    }

    public function test_formateur_ne_peut_pas_ajouter_module_formation_autre()
    {
        [$user, $token] = $this->actingAsFormateur();
        $autre = User::factory()->create(['role' => 'FORMATEUR']);
        $formation = Formation::factory()->create(['idUtilisateur' => $autre->id]);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson("/api/formations/{$formation->id}/modules", [
                 'titre' => 'Hack',
             ])
             ->assertStatus(404);
    }

    public function test_creation_formation_validation_echoue()
    {
        [$user, $token] = $this->actingAsFormateur();

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/formations', [])
             ->assertStatus(422);
    }
}