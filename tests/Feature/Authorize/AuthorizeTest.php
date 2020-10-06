<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Authorize;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
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
        $this->seedSesameStreet();
        $this->actingAs(User::where('name', 'Bert')->first());

        $response = $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Bert',
                ],
                [
                    'name' => 'The Count',
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
        $this->seedSesameStreet();
        $this->actingAs(User::where('name', 'Bert')->first());

        $response = $this->json('GET', 'jory/team/' . Team::first()->id, [
            'jory' => [
                'fld' => 'users.name',
            ],
        ]);

        $expected = [
            'data' => [
                'users' => [
                    [
                        'name' => 'Bert',
                    ],
                    [
                        'name' => 'The Count',
                    ],
                ]
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function the_authorize_method_is_scoped()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();
        $this->actingAs(User::where('name', 'Bert')->first());

        $response = $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'd' => 'Bert',
                ]
            ],
        ]);

        /**
         * If the authorize method wasn't scoped there would be a bug returning
         * more results because of the orWhere in UserJoryResource::authorize().
         */
        $expected = [
            'data' => [
                [
                    'name' => 'Bert',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }
}
