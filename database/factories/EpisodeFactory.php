<?php

namespace Database\Factories;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ArticleFactory.
 */
class EpisodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Episode::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $faker = $this->faker;

        return [
            'tmdb_id' => $faker->unique()->randomNumber(5),
            'numero' => $faker->unique()->randomNumber(2),
            'name' => $faker->name,
            'name_fr' => $faker->name,
            'resume' => $faker->realText,
            'resume_fr' => $faker->realText,
            'diffusion_us' => $faker->date,
            'diffusion_fr' => $faker->date,
            'ba' => $faker->domainName,
            'moyenne' => 15,
            'nbnotes' => 2,
        ];
    }
}
