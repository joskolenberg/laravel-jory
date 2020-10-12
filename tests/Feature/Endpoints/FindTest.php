<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Endpoints;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class FindTest extends TestCase
{

    /** @test */
    public function it_can_return_a_single_record()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => ["name"],
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
            ],
        ]);
    }

    /** @test */
    public function it_returns_a_404_when_a_model_is_not_found_by_id()
    {
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/1', [
            'jory' => [
                'fld' => 'name',
            ]
        ])->assertStatus(404)->assertExactJson([
            'message' => 'No query results for model [' . User::class . '] 1',
        ]);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_single_record()
    {
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/2', [
            'jory' => '}',
        ])->assertStatus(422)
            ->assertExactJson([
            'errors' => [
                'Jory string is no valid json.',
            ],
        ]);
    }

    /** @test */
    public function it_returns_a_404_when_an_unknown_resource_by_id_is_requested()
    {
        $this->json('GET', 'jory/team/2')->assertStatus(404);
    }

    /** @test */
    public function a_find_call_can_be_done_when_fetching_multiple_resources()
    {
        $team1 = $this->seedSesameStreet();
        $team2 = $this->seedSimpsons();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'team:' . $team1->id => [
                    'fld' => 'name',
                ],
                'team:' . $team2->id => [
                    'fld' => 'name',
                ],
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'team:' . $team1->id => [
                    'name' => 'Sesame Street'
                ],
                'team:' . $team2->id => [
                    'name' => 'Simpsons'
                ],
            ],
        ]);
    }

    /** @test */
    public function it_returns_null_when_a_model_is_not_found_by_id_when_loading_multiple_resources()
    {
        $team = $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'team:' . $team->id => [
                    'fld' => 'name',
                ],
                'team:' . ($team->id + 1) => [
                    'fld' => 'name',
                ],
            ]
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'team:' . $team->id => [
                    'name' => 'Sesame Street',
                ],
                'team:' . ($team->id + 1) => null,
            ],
        ]);
    }
}
