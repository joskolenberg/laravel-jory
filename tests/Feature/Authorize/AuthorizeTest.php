<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Authorize;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\NewModels\Team;
use JosKolenberg\LaravelJory\Tests\NewModels\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class AuthorizeTest extends TestCase
{
    protected $beatles = [
        'John',
        'Paul',
        'George',
        'Ringo',
    ];

    /** @test */
    public function it_can_modify_the_query_by_authorize_method()
    {
        Jory::register(UserJoryResource::class);

        foreach ($this->beatles as $name) {
            User::factory()->create(['name' => $name]);
        }

        $response = $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'John',
                ],
                [
                    'name' => 'Paul',
                ],
                [
                    'name' => 'George',
                ],
                [
                    'name' => 'Ringo',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->actingAs(User::where('name', 'John')->first());

        $response = $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'George',
                ],
                [
                    'name' => 'Ringo',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_modify_the_query_by_authorize_method_in_relationsss()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $team = Team::factory()->create(['name' => 'beatles']);

        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $team->id,
            ]);
        }
        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'users.name',
            ],
        ]);

        $expected = [
            'data' => [
                'users' => [
                    [
                        'name' => 'John',
                    ],
                    [
                        'name' => 'Paul',
                    ],
                    [
                        'name' => 'George',
                    ],
                    [
                        'name' => 'Ringo',
                    ],
                ]
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->actingAs(User::where('name', 'John')->first());
        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'users.name',
            ],
        ]);

        $expected = [
            'data' => [
                'users' => [
                    [
                        'name' => 'George',
                    ],
                    [
                        'name' => 'Ringo',
                    ],
                ]
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }
}
