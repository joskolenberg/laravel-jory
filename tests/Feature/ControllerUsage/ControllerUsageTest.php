<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ControllerUsage;

use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\Controllers\BandController;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ControllerUsage\Controllers\TeamController;
use JosKolenberg\LaravelJory\Tests\Feature\ControllerUsage\Controllers\UserController;
use JosKolenberg\LaravelJory\Tests\TestCase;

class ControllerUsageTest extends TestCase
{

    /** @test */
    public function it_can_return_a_collection_based_on_request()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Route::get('user', [UserController::class, 'index'])->middleware('jory');

        $response = $this->json('GET', 'user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%osc%',
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Oscar',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_return_a_single_record_based_on_request()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);
        Route::get('team/{teamId}', [TeamController::class, 'show'])->middleware('jory');

        $response = $this->json('GET', 'team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
            ]
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
            ],
        ]);
    }

    /** @test */
    public function it_can_return_a_single_record_filtered_by_jory()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Route::get('user/first-by-filter', [UserController::class, 'firstByFilter'])->middleware('jory');

        $response = $this->json('GET', 'user/first-by-filter', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'd' => 'Ernie',
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Ernie',
            ],
        ]);
    }

    /** @test */
    public function it_can_return_a_record_count_based_on_jory_filters()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Route::get('user/count', [UserController::class, 'count'])->middleware('jory');

        $response = $this->json('GET', 'user/count', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%r%',
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => 5,
        ]);
    }

    /** @test */
    public function it_does_not_execute_when_no_jory_parameter_is_given()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        Route::get('user', [UserController::class, 'index'])->middleware('jory');
        Route::get('team/{teamId}', [TeamController::class, 'show'])->middleware('jory');
        Route::get('user/first-by-filter', [UserController::class, 'firstByFilter'])->middleware('jory');
        Route::get('user/count', [UserController::class, 'count'])->middleware('jory');

        $this->startQueryCount();

        $response = $this->json('GET', 'user');
        $response->assertStatus(200)->assertExactJson([]);

        $response = $this->json('GET', 'team/2');
        $response->assertStatus(200)->assertExactJson([]);

        $response = $this->json('GET', 'user/first-by-filter');
        $response->assertStatus(200)->assertExactJson([]);

        $response = $this->json('GET', 'user/count');
        $response->assertStatus(200)->assertExactJson([]);

        $this->assertQueryCount(0);
    }

}
