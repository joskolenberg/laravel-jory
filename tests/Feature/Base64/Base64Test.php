<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Base64;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class Base64Test extends TestCase
{
    protected $beatles = [
        'John',
        'Paul',
        'George',
        'Ringo',
    ];

    /** @test */
    public function it_can_process_a_base64_encoded_json_string()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $team = Team::factory()->create(['name' => 'Beatles']);
        Team::factory()->create(['name' => 'Rolling Stones']);

        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $team->id,
            ]);
        }

        $response = $this->json('GET', 'jory/team', [
            'jory' => base64_encode(json_encode([
                'fld' => 'name',
                'flt' =>
                    [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%eat%',
                    ],
                'rlt' =>
                    [
                        'users' =>
                            [
                                'fld' =>
                                    [
                                        'name',
                                    ],
                                'flt' =>
                                    [
                                        'f' => 'name',
                                        'o' => 'like',
                                        'd' => '%e%',
                                    ],
                            ],
                    ],
            ])),
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Beatles',
                    'users' => [
                        [
                            'name' => 'George',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_process_a_base64_encoded_json_string_for_multiple_resources()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $team = Team::factory()->create(['name' => 'Beatles']);
        Team::factory()->create(['name' => 'Rolling Stones']);

        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $team->id,
            ]);
        }

        $response = $this->json('GET', 'jory', [
            'jory' => base64_encode(json_encode([
                'user:first as john' => [
                    'fld' => [
                        'name',
                        'team.name',
                    ],
                ],
                'team:first as beatles' => [
                    'fld' => 'name',
                    'flt' => [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%eat%',
                    ],
                    'rlt' => [
                        'users:count as users_count' => [],
                    ]
                ]
            ])),
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'john' => [
                    'name' => 'John',
                    'team' => [
                        'name' => 'Beatles',
                    ],
                ],
                'beatles' => [
                    'name' => 'Beatles',
                    'users_count' => 4,
                ],
            ],
        ]);
    }
}
