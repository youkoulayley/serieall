<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CommentFactory.
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $faker = $this->faker;

        return [
            'message' => $faker->realText,
            'thumb' => 1,
            'spoiler' => false,
        ];
    }
}
