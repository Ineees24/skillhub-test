<?php

namespace Tests\Unit;

use App\Models\CategorieFormation;
use App\Models\Formation;
use App\Models\Lecon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeconTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecon_appartient_a_une_formation(): void
    {
        $formateur = User::factory()->create(['role' => 'FORMATEUR']);
        $formation = Formation::factory()->create(['idUtilisateur' => $formateur->id]);
        $lecon     = Lecon::factory()->create(['idFormation' => $formation->id]);

        $this->assertInstanceOf(Formation::class, $lecon->formation);
        $this->assertEquals($formation->id, $lecon->formation->id);
    }

    public function test_lecon_fillable(): void
    {
        $lecon = new Lecon();
        $this->assertEquals(['titre', 'duree', 'contenu', 'idFormation'], $lecon->getFillable());
    }

    public function test_categorie_creation(): void
    {
        $categorie = CategorieFormation::factory()->create(['nomCategorie' => 'Informatique']);
        $this->assertDatabaseHas('categorieformation', ['nomCategorie' => 'Informatique']);
    }

    public function test_categorie_fillable(): void
    {
        $categorie = new CategorieFormation();
        $this->assertContains('nomCategorie', $categorie->getFillable());
    }

    public function test_categorie_table(): void
    {
        $categorie = new CategorieFormation();
        $this->assertEquals('categorieformation', $categorie->getTable());
    }
}