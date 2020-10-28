<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Query\OffsetLimit;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class OffsetLimitTest extends TestCase
{
    /** @test */
    public function it_can_apply_an_offset_and_limit()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'ofs' => 2,
                'lmt' => 2,
                'srt' => 'name',
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Cookie Monster',
                ],
                [
                    'name' => 'Ernie',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_a_limit_without_an_offset()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'lmt' => 2,
                'srt' => 'name',
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Bert',
                ],
                [
                    'name' => 'Big Bird',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_an_offset_and_limit_combined_with_with_sorts_and_filters()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'lmt' => 2,
                'srt' => '-name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%e%'
                ]
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'The Count',
                ],
                [
                    'name' => 'Ernie',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_an_offset_and_limit_combined_with_with_sorts_and_filters_on_relations()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'users' => [
                        'fld' => 'name',
                        'lmt' => 2,
                        'srt' => '-name',
                        'flt' => [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%e%'
                        ]
                    ]
                ]
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users' => [
                    [
                        'name' => 'The Count',
                    ],
                    [
                        'name' => 'Ernie',
                    ],
                ]
            ],
        ]);
    }
}
