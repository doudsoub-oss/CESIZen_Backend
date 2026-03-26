<?php

namespace Database\Factories;

use App\Models\Questionnaire;
use App\Models\ResultInterpretation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResultInterpretation>
 */
class ResultInterpretationFactory extends Factory
{
    public function definition(): array
    {
        $minScore = fake()->numberBetween(0, 50);

        return [
            'questionnaire_id' => Questionnaire::factory(),
            'min_score' => $minScore,
            'max_score' => $minScore + fake()->numberBetween(10, 30),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'recommendations' => fake()->paragraph(),
            'color' => fake()->randomElement(['green', 'yellow', 'orange', 'red']),
        ];
    }

    public function low(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_score' => 0,
            'max_score' => 10,
            'title' => 'Niveau de stress faible',
            'color' => 'green',
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_score' => 11,
            'max_score' => 20,
            'title' => 'Niveau de stress modéré',
            'color' => 'yellow',
        ]);
    }

    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_score' => 21,
            'max_score' => 30,
            'title' => 'Niveau de stress élevé',
            'color' => 'red',
        ]);
    }
}
