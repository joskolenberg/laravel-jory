<?php

namespace JosKolenberg\LaravelJory\Tests\Parsers;

use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Tests\Controllers\BandController;
use JosKolenberg\LaravelJory\Tests\Controllers\PersonController;
use JosKolenberg\LaravelJory\Tests\TestCase;

class RequestParserTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Route::get('/person', PersonController::class.'@index');
        Route::get('/band', BandController::class.'@index');
    }

    /** @test */
    public function it_can_get_the_jory_parameter_from_a_request()
    {
        $response = $this->json('GET', '/person',
            [
                'jory' => '{"filter":{"f": "first_name","v":"John"}}',
            ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id'            => 8,
                    'first_name'    => 'John',
                    'last_name'     => 'Bonham',
                    'date_of_birth' => '1948-05-31',
                ],
                [
                    'id'            => 9,
                    'first_name'    => 'John',
                    'last_name'     => 'Lennon',
                    'date_of_birth' => '1940-10-09',
                ],
            ]);
    }

    /** @test */
    public function it_defaults_to_empty_when_no_data_is_passed()
    {
        $response = $this->json('GET', '/band');

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id'   => 1,
                    'name' => 'Rolling Stones',
                ],
                [
                    'id'   => 2,
                    'name' => 'Led Zeppelin',
                ],
                [
                    'id'   => 3,
                    'name' => 'Beatles',
                ],
                [
                    'id'   => 4,
                    'name' => 'Jimi Hendrix Experience',
                ],
            ]);
    }
}
