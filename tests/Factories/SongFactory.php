<?php


namespace JosKolenberg\LaravelJory\Tests\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Album;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Song;

class SongFactory extends Factory
{
    protected $model = Song::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
        ];
    }
}