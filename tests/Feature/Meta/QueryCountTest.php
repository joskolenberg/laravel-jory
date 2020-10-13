<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Meta;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class QueryCountTest extends TestCase
{

    /** @test */
    public function it_can_return_the_query_count_as_meta_data()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $this->startQueryCount();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'd' => 'Bert',
                ],
                'rlt' => [
                    'team' => [
                        'fld' => 'name',
                    ],
                ]
            ],
            'meta' => ['query_count'],
        ])->assertExactJson([
            'data' => [
                [
                    'name' => 'Bert',
                    'team' => [
                        'name' => 'Sesame Street',
                    ],
                ],
            ],
            'meta' => [
                'query_count' => 2
            ]
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_the_query_count_as_meta_data_when_applying_custom_methods()
    {
        $team = $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $this->startQueryCount();

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'users:count' => [],
                ]
            ],
            'meta' => ['query_count'],
        ])->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users:count' => 6,
            ],
            'meta' => [
                'query_count' => 2
            ]
        ]);

        $this->assertQueryCount(2);
    }
}
