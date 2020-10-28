<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Meta;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class TotalTest extends TestCase
{

    /** @test */
    public function it_can_give_the_total_records_for_a_single_resource()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'lmt' => 2,
                'srt' => 'name',
            ],
            'meta' => ['total'],
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird']
            ],
            'meta' => [
                'total' => 6,
            ]
        ]);
    }

    /** @test */
    public function it_gives_null_as_total_records_for_a_count_request()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/count', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'd' => 'Ernie',
                ],
            ],
            'meta' => ['total'],
        ])->assertExactJson([
            'data' => 1,
            'meta' => [
                'total' => null,
            ]
        ]);
    }

    /** @test */
    public function it_gives_null_as_total_records_for_an_exists_request()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/exists', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'd' => 'Ernie',
                ],
            ],
            'meta' => ['total'],
        ])->assertExactJson([
            'data' => true,
            'meta' => [
                'total' => null,
            ]
        ]);
    }

    /** @test */
    public function it_gives_null_as_total_records_for_a_first_request()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/first', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'd' => 'Ernie',
                ],
            ],
            'meta' => ['total'],
        ])->assertExactJson([
            'data' => [
                'name' => 'Ernie'
            ],
            'meta' => [
                'total' => null,
            ]
        ]);
    }

    /** @test */
    public function it_gives_null_as_total_records_for_a_find_request()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => ['total'],
        ])->assertExactJson([
            'data' => [
                'name' => 'Sesame Street'
            ],
            'meta' => [
                'total' => null,
            ]
        ]);
    }

    /** @test */
    public function it_can_give_the_total_records_for_multiple_resources_and_returns_no_total_for_non_collection_requests()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'team' => [
                    'fld' => 'name',
                ],
                'user' => [
                    'fld' => 'name',
                    'lmt' => 2,
                    'srt' => 'name',
                ],
                'team:' . $team->id => [
                    'fld' => 'name',
                ],
            ],
            'meta' => ['total'],
        ])->assertExactJson([
            'data' => [
                'team' => [
                    ['name' => 'Sesame Street']
                ],
                'user' => [
                    ['name' => 'Bert'],
                    ['name' => 'Big Bird'],
                ],
                'team:' . $team->id => [
                    'name' => 'Sesame Street',
                ],
            ],
            'meta' => [
                'total' => [
                    'team' => 1,
                    'user' => 6,
                ],
            ]
        ]);
    }
}
