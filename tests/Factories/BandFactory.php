<?php


namespace JosKolenberg\LaravelJory\Tests\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Band;

class BandFactory extends Factory
{
    protected $model = Band::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
        ];
    }
}