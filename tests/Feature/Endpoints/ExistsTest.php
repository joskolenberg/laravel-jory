<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Endpoints;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class ExistsTest extends TestCase
{

    /** @test */
    public function it_can_tell_if_an_item_exists()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $cases = [
            '%coun%' => true,
            '%Homer%' => false,
            'Bert' => true,
            '%SpongeBob%' => false,
        ];

        foreach ($cases as $name => $result) {
            $response = $this->json('GET', 'jory/user/exists', [
                'jory' => [
                    'flt' => [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => $name,
                    ],
                ]
            ]);

            $expected = [
                'data' => $result,
            ];
            $response->assertStatus(200)->assertExactJson($expected);
        }
    }

    /** @test */
    public function it_can_tell_if_a_relation_exists()
    {
        $this->seedSesameStreet();
        $this->seedSimpsons();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $response = $this->json('GET', 'jory/team', [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'users:exists' => [
                        'flt' => [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%count%',
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Sesame Street',
                    'users:exists' => true,
                ],
                [
                    'name' => 'Simpsons',
                    'users:exists' => false,
                ],
            ],
        ]);
    }

    /** @test */
    public function it_doesnt_fail_when_requesting_exists_on_a_non_collection_relation()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $otherTeam = Team::factory()->create([
            'name' => 'Other team'
        ]);

        User::factory()->create([
            'name' => 'Monster in other team',
            'team_id' => $otherTeam->id,
        ]);

        User::factory()->create([
            'name' => 'Teamless Monster',
            'team_id' => null,
        ]);

        $response = $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'team:exists' => [
                        'flt' => [
                            'f' => 'name',
                            'd' => 'Sesame Street',
                        ]
                    ]
                ],
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%monster%',
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Cookie Monster',
                    'team:exists' => true,
                ],
                [
                    'name' => 'Monster in other team',
                    'team:exists' => false,
                ],
                [
                    'name' => 'Teamless Monster',
                    'team:exists' => false,
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_exists_when_fetching_multiple_resources()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'user:exists as user_exists' => [
                    'flt' => [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%Dexter%',
                    ],
                ],
                'team:exists' => []
            ]
        ]);

        $expected = [
            'data' => [
                'user_exists' => false,
                'team:exists' => true,
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_returns_a_404_when_an_unknown_resource_exists_is_requested()
    {
        $this->json('GET', 'jory/team/exists')->assertStatus(404);
    }

    /** @test */
    public function an_exists_call_can_be_done_when_fetching_multiple_resources()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'team:exists' => [],
                'user:exists' => [
                    'flt' => [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%homer%',
                    ]
                ],
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'team:exists' => true,
                'user:exists' => false,
            ],
        ]);
    }

    /** @test */
    public function an_exists_call_can_be_done_on_relations()
    {
        $team = $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'users:exists' => [
                        'flt' => [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%e%',
                        ]
                    ],
                ],
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users:exists' => true,
            ],
        ]);
    }
}
