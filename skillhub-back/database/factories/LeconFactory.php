<?php

namespace Database\Factories;

use App\Models\Formation;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeconFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titre'       => $this->faker->sentence(4),
            'duree'       => $this->faker->numberBetween(10, 120),
            'contenu'     => $this->faker->paragraph(),
            'idFormation' => Formation::factory(),
        ];
    }
}