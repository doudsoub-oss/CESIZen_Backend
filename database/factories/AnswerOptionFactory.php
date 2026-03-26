<?php

namespace Database\Factories;

use App\Models\AnswerOption;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnswerOption>
 */
class AnswerOptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'label' => fake()->randomElement(['Jamais', 'Rarement', 'Parfois', 'Souvent', 'Toujours']),
            'score' => fake()->numberBetween(0, 4),
            'position' => fake()->numberBetween(0, 5),
        ];
    }
}
