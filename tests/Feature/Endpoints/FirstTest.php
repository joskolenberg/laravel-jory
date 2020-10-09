<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Endpoints;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class FirstTest extends TestCase
{

    /** @test */
    public function it_can_return_the_first_item_using_the_uri()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user/first', [
            'jory' => [
                'fld' => 'name',
            ]
        ])->assertExactJson([
            'data' => [
                'name' => 'Bert',
            ],
        ]);
    }

    /** @test */
    public function it_can_return_the_first_item_on_a_relation_1()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);
        $team = $this->seedSesameStreet();

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'users:first' => [
                        'fld' => 'name',
                    ]
                ]
            ]
        ])->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users:first' => [
                    'name' => 'Bert',
                ]
            ],
        ]);
    }

    /** @test */
    public function it_doesnt_fail_when_requesting_the_first_item_on_a_non_collection_relation()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user/first', [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'team:first' => [
                        'fld' => 'name',
                    ]
                ]
            ]
        ])->assertExactJson([
            'data' => [
                'name' => 'Bert',
                'team:first' => [
                    'name' => 'Sesame Street',
                ]
            ],
        ]);
    }

    /** @test */
    public function it_can_return_the_first_item_when_fetching_multiple_resources()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory', [
            'jory' => [
                'user:first' => [
                    'fld' => 'name',
                ],
                'team:first' => [
                    'fld' => 'name',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                'user:first' => [
                    'name' => 'Bert',
                ],
                'team:first' => [
                    'name' => 'Sesame Street',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_the_first_items_on_nested_relations_but_comes_with_n_plus_1_problem()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();
        $this->seedSpongeBob();

        $this->startQueryCount();

        $this->json('GET', 'jory/team', [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'users:first' => [
                        'fld' => 'name'
                    ]
                ]
            ]
        ])->assertExactJson([
            'data' => [
                [
                    'name' => 'Sesame Street',
                    'users:first' => [
                        'name' => 'Bert'
                    ]
                ],
                [
                    'name' => 'Simpsons',
                    'users:first' => [
                        'name' => 'Bart'
                    ]
                ],
                [
                    'name' => 'SpongeBob',
                    'users:first' => [
                        'name' => 'Eugene'
                    ]
                ],
            ],
        ]);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_returns_a_404_when_a_model_is_not_found_by_first()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user/first', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'd' => 'Homer',
                ]
            ]
        ])->assertStatus(404)->assertExactJson([
            'message' => 'No query results for model [' . User::class . '].',
        ]);
    }
}
