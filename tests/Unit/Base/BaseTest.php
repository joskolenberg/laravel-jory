<?php

namespace JosKolenberg\LaravelJory\Tests\Unit\Base;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\BandJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\MusicianJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class BaseTest extends TestCase
{
    /** @test */
    public function it_can_apply_a_jory_json_string()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $actual = Jory::onModelClass(User::class)
            ->applyJson(json_encode([
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%n%',
                ]
            ]))
            ->toArray();

        $this->assertEquals([
            ['name' => 'Cookie Monster'],
            ['name' => 'Ernie'],
            ['name' => 'The Count'],
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_array()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $actual = Jory::onModelClass(User::class)
            ->applyArray([
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%n%',
                ]
            ])
            ->toArray();

        $this->assertEquals([
            ['name' => 'Cookie Monster'],
            ['name' => 'Ernie'],
            ['name' => 'The Count'],
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_json_string_from_a_request()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%n%',
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                ['name' => 'Cookie Monster'],
                ['name' => 'Ernie'],
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_a_custom_filter()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();

        $actual = Jory::onModelClass(Team::class)->applyArray([
            'flt' => [
                'f' => 'number_of_users',
                'o' => '<',
                'd' => 6,
            ],
            'fld' => ['name'],
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Simpsons'],
        ], $actual);
    }

    /** @test */
    public function it_can_apply_multiple_custom_filters()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();
        $this->seedSpongeBob();

        $actual = Jory::onModelClass(Team::class)->applyArray([
            'flt' => [
                'or' => [
                    [
                        'f' => 'number_of_users',
                        'o' => '>',
                        'd' => 5,
                    ],
                    [
                        'f' => 'number_of_users',
                        'o' => '<',
                        'd' => 5,
                    ],
                ]
            ],
            'fld' => ['name'],
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Sesame Street'],
            ['name' => 'SpongeBob'],
        ], $actual);
    }

    /** @test */
    public function it_can_combine_standard_and_custom_filters()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();
        $this->seedSpongeBob();

        $actual = Jory::onModelClass(Team::class)->applyArray([
            'flt' => [
                'or' => [
                    [
                        'f' => 'number_of_users',
                        'o' => '>',
                        'd' => 5,
                    ],
                    [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%imp%',
                    ],
                ]
            ],
            'fld' => ['name'],
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Sesame Street'],
            ['name' => 'Simpsons'],
        ], $actual);
    }

    /** @test */
    public function it_can_override_the_basic_filter_function()
    {
        Jory::register(\JosKolenberg\LaravelJory\Tests\Unit\Base\UserJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();
        $this->seedSpongeBob();

        $actual = Jory::onModelClass(User::class)
            ->applyArray([
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%e%',
                ],
                'fld' => ['name']
            ])->toArray();

        $this->assertEquals([
            ['name' => 'Bert'],
            ['name' => 'Cookie Monster'],
            ['name' => 'Ernie'],
            ['name' => 'The Count'],
            // An extra custom filter is made to include only Sesame Street, so other users should be missing
        ], $actual);
    }

    /** @test */
    public function it_can_return_a_single_model()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $actual = Jory::onModelClass(User::class)
            ->applyArray([
                'fld' => ['name']
            ])
            ->first()
            ->toArray();

        $this->assertEquals([
            'name' => 'Bert',
        ], $actual);
    }

    /** @test */
    public function it_returns_null_when_a_single_model_is_not_found()
    {
        Jory::register(UserJoryResource::class);
        $actual = Jory::onModelClass(User::class)
            ->applyArray([
                'flt' => [
                    'f' => 'name',
                    'd' => 'John',
                ],
            ])->first()
            ->toArray();

        $this->assertNull($actual);
    }

    /** @test */
    public function it_can_filter_sort_and_select_on_an_ambiguous_column_when_using_a_belongs_to_many_relation()
    {
        Jory::register(BandJoryResource::class);
        Jory::register(MusicianJoryResource::class);
        $this->seedBeatles();
        $this->seedStones();
        $this->seedHendrix();

        $this->startQueryCount();

        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%t%',
                ],
                'srt' => '-name',
                'fld' => ['name'],
                'rlt' => [
                    'musicians' => [
                        'fld' => ['name'],
                        'flt' => [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%e%',
                        ],
                        'srt' => '-name'
                    ]
                ]
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Stones',
                    'musicians' => [
                        [
                            'name' => 'Ronnie',
                        ],
                        [
                            'name' => 'Keith',
                        ],
                        [
                            'name' => 'Charlie',
                        ],
                    ],
                ],
                [
                    'name' => 'Beatles',
                    'musicians' => [
                        [
                            'name' => 'George',
                        ],
                    ],
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }
}
