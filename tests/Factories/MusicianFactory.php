<?php


namespace JosKolenberg\LaravelJory\Tests\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Musician;

class MusicianFactory extends Factory
{
    protected $model = Musician::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}