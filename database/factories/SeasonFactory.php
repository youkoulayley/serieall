<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ArticleFactory.
 */
class SeasonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Season::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $faker = $this->faker;

        return [
            'tmdb_id' => $faker->unique()->randomNumber(5),
            'name' => $faker->unique()->randomDigit(),
            'ba' => $faker->name,
            'moyenne' => 15,
            'nbnotes' => 2,
        ];
    }
}
