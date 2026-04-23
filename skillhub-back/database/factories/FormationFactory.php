<?php

namespace Database\Factories;

use App\Models\Formation;
use App\Models\User;
use App\Models\CategorieFormation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormationFactory extends Factory
{
    protected $model = Formation::class;

    public function definition(): array
    {
        return [
            'titre'         => fake()->sentence(4),
            'description'   => fake()->paragraph(),
            'duree'         => fake()->randomElement([2, 3, 4, 6, 8]),
            'prix'          => fake()->randomFloat(2, 10, 300),
            'niveauRequis'  => fake()->randomElement(['Débutant', 'Intermédiaire', 'Avancé', 'Tous niveaux']),
            'statut'        => fake()->randomElement(['publié', 'brouillon']),
            'idUtilisateur' => User::factory(),
            'idCategorie'   => CategorieFormation::factory(),
        ];
    }
}