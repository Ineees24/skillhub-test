<?php

namespace Database\Factories;

use App\Models\CategorieFormation;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategorieFormationFactory extends Factory
{
    protected $model = CategorieFormation::class;

    public function definition(): array
    {
        return [
            'nomCategorie' => fake()->randomElement([
                'Développement Web',
                'Design',
                'Marketing',
                'Data Science',
                'DevOps',
                'Mobile',
            ]),
        ];
    }
}