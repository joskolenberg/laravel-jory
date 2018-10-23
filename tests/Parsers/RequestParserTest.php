<?php

namespace JosKolenberg\LaravelJory\Tests\Parsers;

use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Tests\TestCase;
use JosKolenberg\LaravelJory\Tests\Controllers\BandController;
use JosKolenberg\LaravelJory\Tests\Controllers\PersonController;

class RequestParserTest extends TestCase
{
    /** @test */
    public function it_can_get_the_jory_parameter_from_a_request()
    {
        $response = $this->json('GET', 'jory/person', [
            'jory' => '{"filter":{"f": "first_name","v":"John"},"fld":["id","last_name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            [
                'id' => 8,
                'last_name' => 'Bonham',
            ],
            [
                'id' => 9,
                'last_name' => 'Lennon',
            ],
        ]);
    }

    /** @test */
    public function it_defaults_to_empty_when_no_data_is_passed()
    {
        $response = $this->json('GET', 'jory/band');

        $response->assertStatus(200)->assertExactJson([
            [
                'id' => 1,
                'name' => 'Rolling Stones',
                'year_start' => 1962,
                'year_end' => null,
            ],
            [
                'id' => 2,
                'name' => 'Led Zeppelin',
                'year_start' => 1968,
                'year_end' => 1980,
            ],
            [
                'id' => 3,
                'name' => 'Beatles',
                'year_start' => 1960,
                'year_end' => 1970,
            ],
            [
                'id' => 4,
                'name' => 'Jimi Hendrix Experience',
                'year_start' => 1966,
                'year_end' => 1970,
            ],
        ]);
    }
}
