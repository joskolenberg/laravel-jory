<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Tests\Controllers\BandController;

class ControllerUsageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('band', BandController::class.'@index')->middleware('jory');
        Route::get('band/first-by-filter', BandController::class.'@firstByFilter')->middleware('jory');
        Route::get('band/count', BandController::class.'@count')->middleware('jory');
        Route::get('band/{bandId}', BandController::class.'@show')->middleware('jory');
    }

    /** @test */
    public function it_can_return_a_collection_based_on_request()
    {
        $response = $this->json('GET', 'band', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%zep%',
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_a_single_record_based_on_request()
    {
        $response = $this->json('GET', 'band/2', [
            'jory' => []
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'id' => 2,
                'name' => 'Led Zeppelin',
                'year_start' => 1968,
                'year_end' => 1980,
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_a_single_record_filtered_by_jory()
    {
        $response = $this->json('GET', 'band/first-by-filter', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'd' => 'Beatles',
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'id' => 3,
                'name' => 'Beatles',
                'year_start' => 1960,
                'year_end' => 1970,
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_a_record_count_based_on_jory_filters()
    {
        $response = $this->json('GET', 'band/count', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%r%',
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => 2,
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_does_not_execute_when_no_jory_parameter_is_given()
    {
        $response = $this->json('GET', 'band/2');
        $response->assertStatus(200)->assertExactJson([]);

        $response = $this->json('GET', 'band/first-by-filter');
        $response->assertStatus(200)->assertExactJson([]);

        $response = $this->json('GET', 'band');
        $response->assertStatus(200)->assertExactJson([]);

        $response = $this->json('GET', 'band/count');
        $response->assertStatus(200)->assertExactJson([]);

        $this->assertQueryCount(0);
    }

}
