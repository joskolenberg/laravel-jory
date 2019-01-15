<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class JoryBuilderFilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_apply_a_single_filter()
    {
        $actual = Person::jory()->applyArray([
            'filter' => [
                'field' => 'first_name',
                'operator' => 'like',
                'data' => '%john%',
            ],
        ])->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Jones', 'Bonham', 'Lennon'], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_an_OR_filter_group()
    {
        $actual = Person::jory()->applyArray([
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
        ])->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Jones', 'Lennon', 'McCartney'], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_an_AND_filter_group()
    {
        $actual = Person::jory()->applyArray([
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
        ])->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Lennon'], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_1()
    {
        $actual = Song::jory()->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field' => 'title',
                        'operator' => 'like',
                        'data' => '%love%',
                    ],
                ],
            ],
        ])->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Whole Lotta Love',
            'Lovely Rita',
            'Love or Confusion',
            'May This Be Love',
            'Little Miss Lover',
            'Bold as Love',
            'And the Gods Made Love',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_2()
    {
        $actual = Song::jory()->applyArray([
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
        ])->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Little Miss Lover',
            'Bold as Love',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_3()
    {
        $actual = Song::jory()->applyArray([
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
        ])->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'May This Be Love',
            'Little Miss Lover',
            'Bold as Love',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_4()
    {
        $actual = Song::jory()->applyArray([
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
        ])->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Whole Lotta Love',
            'Little Miss Lover',
            'Bold as Love',
            'And the Gods Made Love',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_doesnt_apply_any_filter_when_parameter_is_omitted()
    {
        $actual = Band::jory()->applyArray([])->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_by_the_default_laravel_operators()
    {
        // =, >, <, <>, !=, like, not like, <=, >=
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '=',
                'd' => 'Beatles',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '>',
                'd' => 'KISS',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<',
                'd' => 'Cult',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<>',
                'd' => 'Beatles',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '!=',
                'd' => 'Beatles',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => 'Beat%',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => '%Stones',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => '%s%',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Beatles',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'not like',
                'd' => '%s%',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => '>=',
                'd' => '3',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);

        $this->assertQueryCount(10);
    }

    /** @test */
    public function it_can_filter_on_null_values()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'is_null',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_on_non_null_values()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'not_null',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_an_IN_filter()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'in',
                'd' => [1, 3],
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Beatles',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_NOT_IN_filter()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'not_in',
                'd' => [1, 3],
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_defaults_to_an_EQUALS_check_if_no_operator_is_given()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'd' => 'Beatles',
            ],
        ])->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Beatles',
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
}
