<?php


namespace JosKolenberg\LaravelJory\Tests\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Album;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Image;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition()
    {
        return [
            'url' => $this->faker->url,
        ];
    }
}