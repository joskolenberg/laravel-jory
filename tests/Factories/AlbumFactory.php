<?php


namespace JosKolenberg\LaravelJory\Tests\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Album;

class AlbumFactory extends Factory
{
    protected $model = Album::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}