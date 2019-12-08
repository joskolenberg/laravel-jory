<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\PersonJoryResourceWithScopes;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class FilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_apply_a_single_filter()
    {
        $actual = Jory::onModelClass(Person::class)->applyArray([
            'filter' => [
                'field' => 'first_name',
                'operator' => 'like',
                'data' => '%john%',
            ],
            'fld' => ['last_name'],
        ])->toArray();

        $this->assertEquals([
            ['last_name' => 'Jones'],
            ['last_name' => 'Bonham'],
            ['last_name' => 'Lennon'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_an_OR_filter_group()
    {
        $actual = Jory::onModelClass(Person::class)->applyArray([
            'filter' => [
                'group_or' => [
                    [
                        'field' => 'first_name',
                        'operator' => 'like',
                        'data' => '%paul%',
                    ],
                    [
                        'field' => 'last_name',
                        'operator' => 'like',
                        'data' => '%le%',
                    ],
                ],
            ],
            'fld' => ['last_name'],
        ])->toArray();

        $this->assertEquals([
            ['last_name' => 'Jones'],
            ['last_name' => 'Lennon'],
            ['last_name' => 'McCartney'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_an_AND_filter_group()
    {
        $actual = Jory::onModelClass(Person::class)->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field' => 'first_name',
                        'operator' => 'like',
                        'data' => '%john%',
                    ],
                    [
                        'field' => 'last_name',
                        'operator' => 'like',
                        'data' => '%le%',
                    ],
                ],
            ],
            'fld' => ['last_name'],
        ])->toArray();

        $this->assertEquals([
            ['last_name' => 'Lennon'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_1()
    {
        $actual = Jory::onModelClass(Song::class)->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field' => 'title',
                        'operator' => 'like',
                        'data' => '%love%',
                    ],
                ],
            ],
            'fld' => ['title'],
        ])->toArray();

        $this->assertEquals([
            ['title' => 'Love In Vain (Robert Johnson)'],
            ['title' => 'Whole Lotta Love'],
            ['title' => 'Lovely Rita'],
            ['title' => 'Love or Confusion'],
            ['title' => 'May This Be Love'],
            ['title' => 'Little Miss Lover'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_2()
    {
        $actual = Jory::onModelClass(Song::class)->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field' => 'title',
                        'operator' => 'like',
                        'data' => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'data' => '%bold%',
                            ],
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'data' => '%er%',
                            ],
                        ],
                    ],
                ],
            ],
            'fld' => ['title'],
        ])->toArray();

        $this->assertEquals([
            ['title' => 'Love In Vain (Robert Johnson)'],
            ['title' => 'Little Miss Lover'],
            ['title' => 'Bold as Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_3()
    {
        $actual = Jory::onModelClass(Song::class)->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field' => 'title',
                        'operator' => 'like',
                        'data' => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'data' => '%bold%',
                            ],
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'data' => '%er%',
                            ],
                            [
                                'group_and' => [
                                    [
                                        'field' => 'title',
                                        'operator' => 'like',
                                        'data' => 'may%',
                                    ],
                                    [
                                        'field' => 'title',
                                        'operator' => 'like',
                                        'data' => '%love',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'fld' => ['title'],
        ])->toArray();

        $this->assertEquals([
            ['title' => 'Love In Vain (Robert Johnson)'],
            ['title' => 'May This Be Love'],
            ['title' => 'Little Miss Lover'],
            ['title' => 'Bold as Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_4()
    {
        $actual = Jory::onModelClass(Song::class)->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field' => 'title',
                        'operator' => 'like',
                        'data' => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'data' => '%bold%',
                            ],
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'data' => '%er%',
                            ],
                            [
                                'group_and' => [
                                    [
                                        'field' => 'title',
                                        'operator' => 'like',
                                        'data' => '%e%',
                                    ],
                                    [
                                        'field' => 'title',
                                        'operator' => 'like',
                                        'data' => '%a%',
                                    ],
                                    [
                                        'group_or' => [
                                            [
                                                'field' => 'title',
                                                'operator' => 'like',
                                                'data' => '%whole%',
                                            ],
                                            [
                                                'field' => 'title',
                                                'operator' => 'like',
                                                'data' => '%gods%',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'fld' => ['title'],
        ])->toArray();

        $this->assertEquals([
            ['title' => 'Love In Vain (Robert Johnson)'],
            ['title' => 'Whole Lotta Love'],
            ['title' => 'Little Miss Lover'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_doesnt_apply_any_filter_when_parameter_is_omitted()
    {
        $actual = Jory::onModelClass(Band::class)->applyArray(['fld' => ['name']])->toArray();

        $this->assertEquals([
            ['name' => 'Rolling Stones'],
            ['name' => 'Led Zeppelin'],
            ['name' => 'Beatles'],
            ['name' => 'Jimi Hendrix Experience'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_by_the_default_laravel_operators()
    {
        // =, >, <, <>, !=, like, not_like, <=, >=
        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '=',
                'd' => 'Beatles',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Beatles'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '>',
                'd' => 'KISS',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Rolling Stones'],
            ['name' => 'Led Zeppelin'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<',
                'd' => 'Cult',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Beatles'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<>',
                'd' => 'Beatles',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Rolling Stones'],
            ['name' => 'Led Zeppelin'],
            ['name' => 'Jimi Hendrix Experience'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '!=',
                'd' => 'Beatles',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Rolling Stones'],
            ['name' => 'Led Zeppelin'],
            ['name' => 'Jimi Hendrix Experience'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => 'Beat%',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Beatles'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => '%Stones',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Rolling Stones'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => '%s%',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Rolling Stones'],
            ['name' => 'Beatles'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'not_like',
                'd' => '%s%',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Led Zeppelin'],
            ['name' => 'Jimi Hendrix Experience'],
        ], $actual);

        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => '>=',
                'd' => '3',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Beatles'],
            ['name' => 'Jimi Hendrix Experience'],
        ], $actual);

        $this->assertQueryCount(10);
    }

    /** @test */
    public function it_can_filter_on_null_values()
    {
        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'is_null',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Rolling Stones'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_on_non_null_values()
    {
        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'not_null',
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Led Zeppelin'],
            ['name' => 'Beatles'],
            ['name' => 'Jimi Hendrix Experience'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_an_IN_filter()
    {
        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'in',
                'd' => [1, 3],
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Rolling Stones'],
            ['name' => 'Beatles'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_NOT_IN_filter()
    {
        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'not_in',
                'd' => [1, 3],
            ],
            'fld' => ['name'],
        ])->toArray();
        $this->assertEquals([
            ['name' => 'Led Zeppelin'],
            ['name' => 'Jimi Hendrix Experience'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_defaults_to_an_EQUALS_check_if_no_operator_is_given()
    {
        $actual = Jory::onModelClass(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'd' => 'Beatles',
            ],
            'fld' => ['name'],
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Beatles'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_filter_by_a_local_scope_on_the_related_model()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"fields":["id","name"],"flt":{"f":"has_album_with_name","o":"like","d":"%a%"}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                ],
                [
                    'id' => 3,
                    'name' => 'Beatles',
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_wraps_a_closure_around_custom_orWheres_to_prevent_returning_unwanted_data()
    {
        $response = $this->json('GET', 'jory/person', [
            'jory' => '{"fields":["full_name"],"flt":{"and":[{"f":"full_name","d":"%john%"},{"f":"id","o":"in","d":[7,8]}]}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'full_name' => 'John Paul Jones',
                ],
                [
                    'full_name' => 'John Bonham',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_on_a_related_models_field()
    {
        $response = $this->json('GET', 'jory/person', [
            'jory' => [
                'flt' => [
                    'and' => [
                        [
                            'f' => 'band.albums.songs.title',
                            'd' => 'Tangerine',
                        ],
                        [
                            'f' => 'instruments.name',
                            'o' => 'like',
                            'd' => '%guitar%',
                        ],
                    ],
                ],
                'fld' => [
                    'full_name',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'full_name' => 'John Paul Jones',
                ],
                [
                    'full_name' => 'Jimmy Page',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_on_a_related_models_field_in_default_case()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'flt' => [
                    'or' => [
                        [
                            'f' => 'albumCover.album_id',
                            'o' => '=',
                            'd' => 6,
                        ],
                        [
                            'f' => 'albumCover.album_id',
                            'o' => '=',
                            'd' => 1,
                        ],
                    ],
                ],
                'fld' => [
                    'name',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Let it bleed',
                ],
                [
                    'name' => 'Led Zeppelin III',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_on_a_related_models_field_in_snake_case()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'flt' => [
                    'or' => [
                        [
                            'f' => 'album_cover.album_id',
                            'o' => '=',
                            'd' => 6,
                        ],
                        [
                            'f' => 'album_cover.album_id',
                            'o' => '=',
                            'd' => 1,
                        ],
                    ],
                ],
                'fld' => [
                    'name',
                ],
            ],
            'case' => 'snake',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Let it bleed',
                ],
                [
                    'name' => 'Led Zeppelin III',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_on_a_related_models_field_in_camel_case()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'flt' => [
                    'or' => [
                        [
                            'f' => 'albumCover.albumId',
                            'o' => '=',
                            'd' => 6,
                        ],
                        [
                            'f' => 'albumCover.albumId',
                            'o' => '=',
                            'd' => 1,
                        ],
                    ],
                ],
                'fld' => [
                    'name',
                ],
            ],
            'case' => 'camel',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Let it bleed',
                ],
                [
                    'name' => 'Led Zeppelin III',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_using_a_filter_scope_class()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'flt' => [
                    'f' => 'hasSmallId',
                    'o' => '=',
                    'd' => 6,
                ],
                'fld' => [
                    'name',
                ],
            ],
            'case' => 'camel'
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Let it bleed',
                ],
                [
                    'name' => 'Sticky Fingers',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_via_the_field_using_a_filter_scope_class()
    {
        Jory::register(PersonJoryResourceWithScopes::class);

        $response = $this->json('GET', 'jory/person', [
            'jory' => [
                'flt' => [
                    'f' => 'firstName',
                ],
                'fld' => [
                    'lastName',
                ],
            ],
            'case' => 'camel'
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'lastName' => 'Bonham',
                ],
                [
                    'lastName' => 'Lennon',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_via_the_field_using_a_filter_scope_class_when_requesting_a_relation()
    {
        Jory::register(PersonJoryResourceWithScopes::class);

        $response = $this->json('GET', 'jory/band/3', [
            'jory' => [
                'fld' => [
                    'name',
                ],
                'rlt' => [
                    'people' => [
                        'fld' => ['lastName'],
                        'flt' => [
                            'f' => 'firstName',
                        ],
                    ]
                ]
            ],
            'case' => 'camel'
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Beatles',
                'people' => [
                    [
                        'lastName' => 'Lennon',
                    ],
                ]
            ],
        ]);

        $this->assertQueryCount(2);
    }
}
