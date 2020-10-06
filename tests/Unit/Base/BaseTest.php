<?php

namespace JosKolenberg\LaravelJory\Tests\Unit\Base;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Facades\Jory as Facade;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;
use JosKolenberg\LaravelJory\Tests\Models\SubFolder\Album;
use JosKolenberg\LaravelJory\Tests\TestCase;

class BaseTest extends TestCase
{
    protected $beatles = [
        'John',
        'Paul',
        'George',
        'Ringo',
    ];

    protected $stones = [
        'Mick',
        'Keith',
        'Ronnie',
        'Charlie',
        'Bill',
    ];

    protected $hendrix = [
        'Jimi',
        'Mitch',
        'Noel',
    ];

    /** @test */
    public function it_can_apply_a_jory_json_string()
    {
        Jory::register(UserJoryResource::class);
        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
            ]);
        }

        $actual = Facade::onModelClass(User::class)
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
            ['name' => 'John'],
            ['name' => 'Ringo'],
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_array()
    {
        Jory::register(UserJoryResource::class);
        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
            ]);
        }

        $actual = Facade::onModelClass(User::class)
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
            ['name' => 'John'],
            ['name' => 'Ringo'],
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_json_string_from_a_request()
    {
        Jory::register(UserJoryResource::class);
        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
            ]);
        }

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
                ['name' => 'John'],
                ['name' => 'Ringo'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_a_custom_filter()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $beatles = Team::factory()->create(['name' => 'Beatles']);
        $stones = Team::factory()->create(['name' => 'Stones']);
        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $beatles->id,
            ]);
        }
        foreach ($this->stones as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $stones->id,
            ]);
        }

        $actual = Facade::onModelClass(Team::class)->applyArray([
            'flt' => [
                'f' => 'number_of_users',
                'o' => '>',
                'd' => 4,
            ],
            'fld' => ['name'],
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Stones'],
        ], $actual);
    }

    /** @test */
    public function it_can_apply_multiple_custom_filters()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $beatles = Team::factory()->create(['name' => 'Beatles']);
        $stones = Team::factory()->create(['name' => 'Stones']);
        $hendrix = Team::factory()->create(['name' => 'Hendrix']);
        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $beatles->id,
            ]);
        }
        foreach ($this->stones as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $stones->id,
            ]);
        }
        foreach ($this->hendrix as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $hendrix->id,
            ]);
        }

        $actual = Facade::onModelClass(Team::class)->applyArray([
            'flt' => [
                'or' => [
                    [
                        'f' => 'number_of_users',
                        'o' => '>',
                        'd' => 4,
                    ],
                    [
                        'f' => 'number_of_users',
                        'o' => '<',
                        'd' => 4,
                    ],
                ]
            ],
            'fld' => ['name'],
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Stones'],
            ['name' => 'Hendrix'],
        ], $actual);
    }

    /** @test */
    public function it_can_combine_standard_and_custom_filters()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $beatles = Team::factory()->create(['name' => 'Beatles']);
        $stones = Team::factory()->create(['name' => 'Stones']);
        $hendrix = Team::factory()->create(['name' => 'Hendrix']);
        foreach ($this->beatles as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $beatles->id,
            ]);
        }
        foreach ($this->stones as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $stones->id,
            ]);
        }
        foreach ($this->hendrix as $name) {
            User::factory()->create([
                'name' => $name,
                'team_id' => $hendrix->id,
            ]);
        }

        $actual = Facade::onModelClass(Team::class)->applyArray([
            'flt' => [
                'or' => [
                    [
                        'f' => 'number_of_users',
                        'o' => '>',
                        'd' => 4,
                    ],
                    [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%eat%',
                    ],
                ]
            ],
            'fld' => ['name'],
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Beatles'],
            ['name' => 'Stones'],
        ], $actual);
    }

    /** @test */
    public function it_can_override_the_basic_filter_function()
    {
        $actual = Facade::onModelClass(Instrument::class)
            ->applyArray([
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%t%',
                ],
                'fld' => ['name']
            ])->toArray();

        $this->assertEquals([
            ['name' => 'Guitar'],
            ['name' => 'Bassguitar'],
            // An extra custom filter is made to exclude instruments without connected people, so flute should be missing
        ], $actual);

        $this->assertQueryCount(1);
    }

    public function it_can_return_a_single_model()
    {
        $actual = Facade::onModelClass(Instrument::class)
            ->applyArray([
                'fld' => ['id', 'name']
            ])
            ->first()
            ->toArray();

        $this->assertEquals([
            'id' => 1,
            'name' => 'Vocals',
        ], $actual);

        $this->assertQueryCount(1);
    }

    public function it_returns_null_when_a_single_model_is_not_found()
    {
        $actual = Facade::onModelClass(Instrument::class)
            ->applyArray([
                'flt' => [
                    'f' => 'name',
                    'd' => 'Hobo',
                ],
            ])->first()
            ->toArray();

        $this->assertNull($actual);

        $this->assertQueryCount(1);
    }

    public function it_can_filter_sort_and_select_on_an_ambiguous_column_when_using_a_belongs_to_many_relation()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'flt' => [
                    'f' => 'id',
                    'o' => '>',
                    'd' => 2,
                ],
                'srt' => '-id',
                'fld' => ['id', 'name'],
                'rlt' => [
                    'people' => [
                        'fld' => ['id', 'last_name'],
                        'flt' => [
                            'f' => 'id',
                            'o' => '<',
                            'd' => 14,
                        ],
                        'srt' => '-id'
                    ]
                ]
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'people' => [
                        [
                            'id' => 13,
                            'last_name' => 'Hendrix',
                        ],
                    ],
                ],
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'people' => [
                        [
                            'id' => 12,
                            'last_name' => 'Starr',
                        ],
                        [
                            'id' => 11,
                            'last_name' => 'Harrison',
                        ],
                        [
                            'id' => 10,
                            'last_name' => 'McCartney',
                        ],
                        [
                            'id' => 9,
                            'last_name' => 'Lennon',
                        ],
                    ],
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }
}
