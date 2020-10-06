<?php


namespace JosKolenberg\LaravelJory\Tests\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use JosKolenberg\LaravelJory\Tests\NewModels\Team;
use JosKolenberg\LaravelJory\Tests\NewModels\User;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
        ];
    }
}