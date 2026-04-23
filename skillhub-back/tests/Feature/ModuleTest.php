<?php

namespace Tests\Feature;

use App\Models\Formation;
use App\Models\Lecon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleTest extends TestCase
{
    use RefreshDatabase;

    private function formateur(): User
    {
        return User::factory()->create(['role' => 'FORMATEUR']);
    }

    private function formation(User $formateur): Formation
    {
        return Formation::factory()->create(['idUtilisateur' => $formateur->id]);
    }

    public function test_formateur_voit_modules_de_sa_formation(): void
    {
        $formateur = $this->formateur();
        $formation = $this->formation($formateur);
        Lecon::factory()->count(3)->create(['idFormation' => $formation->id]);

        $token = auth('api')->login($formateur);

        $this->withToken($token)->getJson("/api/formations/{$formation->id}/modules")
             ->assertStatus(200);
    }

    public function test_formateur_ne_voit_pas_modules_dun_autre(): void
    {
        $formateur1 = $this->formateur();
        $formateur2 = $this->formateur();
        $formation  = $this->formation($formateur1);

        $token = auth('api')->login($formateur2);

        $this->withToken($token)->getJson("/api/formations/{$formation->id}/modules")
             ->assertStatus(404);
    }

    public function test_acces_modules_sans_token(): void
    {
        $formateur = $this->formateur();
        $formation = $this->formation($formateur);

        $this->getJson("/api/formations/{$formation->id}/modules")
             ->assertStatus(401);
    }

    public function test_formateur_peut_ajouter_module(): void
    {
        $formateur = $this->formateur();
        $formation = $this->formation($formateur);
        $token     = auth('api')->login($formateur);

        $this->withToken($token)->postJson("/api/formations/{$formation->id}/modules", [
            'titre'   => 'Introduction',
            'duree'   => 30,
            'contenu' => 'Contenu du module',
        ])->assertStatus(201)
          ->assertJsonFragment(['titre' => 'Introduction']);

        $this->assertDatabaseHas('lecon', [
            'titre'       => 'Introduction',
            'idFormation' => $formation->id,
        ]);
    }

    public function test_ajout_module_sans_titre_echoue(): void
    {
        $formateur = $this->formateur();
        $formation = $this->formation($formateur);
        $token     = auth('api')->login($formateur);

        $this->withToken($token)->postJson("/api/formations/{$formation->id}/modules", [
            'duree' => 30,
        ])->assertStatus(422);
    }

    public function test_formateur_ne_peut_pas_ajouter_module_a_formation_dun_autre(): void
    {
        $formateur1 = $this->formateur();
        $formateur2 = $this->formateur();
        $formation  = $this->formation($formateur1);
        $token      = auth('api')->login($formateur2);

        $this->withToken($token)->postJson("/api/formations/{$formation->id}/modules", [
            'titre' => 'Module intrus',
        ])->assertStatus(404);
    }
}