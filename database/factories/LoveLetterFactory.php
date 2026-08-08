<?php

namespace Database\Factories;

use App\Enums\LoveLetterMood;
use App\Models\LoveLetter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoveLetter>
 */
class LoveLetterFactory extends Factory
{
    protected $model = LoveLetter::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->unique()->sentence(4),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(2)).'</p>',
            'mood' => fake()->randomElement(LoveLetterMood::values()),
            'letter_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'is_pinned' => fake()->boolean(20),
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }
}
