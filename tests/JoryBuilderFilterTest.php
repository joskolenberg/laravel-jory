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
                'value' => '%john%',
            ],
        ])->getModels()->pluck('last_name')->toArray();

        $this->assertEquals(['Jones', 'Bonham', 'Lennon'], $actual);
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
                        'value' => '%paul%',
                    ],
                    [
                        'field' => 'last_name',
                        'operator' => 'like',
                        'value' => '%le%',
                    ],
                ],
            ],
        ])->getModels()->pluck('last_name')->toArray();

        $this->assertEquals(['Jones', 'Lennon', 'McCartney'], $actual);
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
                        'value' => '%john%',
                    ],
                    [
                        'field' => 'last_name',
                        'operator' => 'like',
                        'value' => '%le%',
                    ],
                ],
            ],
        ])->getModels()->pluck('last_name')->toArray();

        $this->assertEquals(['Lennon'], $actual);
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
                        'value' => '%love%',
                    ],
                ],
            ],
        ])->getModels()->pluck('title')->toArray();

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
                        'value' => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'value' => '%bold%',
                            ],
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'value' => '%er%',
                            ],
                        ],
                    ],
                ],
            ],
        ])->getModels()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Little Miss Lover',
            'Bold as Love',
        ], $actual);
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
                        'value' => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'value' => '%bold%',
                            ],
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'value' => '%er%',
                            ],
                            [
                                'group_and' => [
                                    [
                                        'field' => 'title',
                                        'operator' => 'like',
                                        'value' => 'may%',
                                    ],
                                    [
                                        'field' => 'title',
                                        'operator' => 'like',
                                        'value' => '%love',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])->getModels()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'May This Be Love',
            'Little Miss Lover',
            'Bold as Love',
        ], $actual);
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
                        'value' => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'value' => '%bold%',
                            ],
                            [
                                'field' => 'title',
                                'operator' => 'like',
                                'value' => '%er%',
                            ],
                            [
                                'group_and' => [
                                    [
                                        'field' => 'title',
                                        'operator' => 'like',
                                        'value' => '%e%',
                                    ],
                                    [
                                        'field' => 'title',
                                        'operator' => 'like',
                                        'value' => '%a%',
                                    ],
                                    [
                                        'group_or' => [
                                            [
                                                'field' => 'title',
                                                'operator' => 'like',
                                                'value' => '%whole%',
                                            ],
                                            [
                                                'field' => 'title',
                                                'operator' => 'like',
                                                'value' => '%gods%',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])->getModels()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Whole Lotta Love',
            'Little Miss Lover',
            'Bold as Love',
            'And the Gods Made Love',
        ], $actual);
    }

    /**
     * @test
     */
    public function it_doesnt_apply_any_filter_when_parameter_is_omitted()
    {
        $actual = Band::jory()->applyArray([])->getModels()->pluck('name')->toArray();

        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_can_filter_by_the_default_laravel_operators()
    {
        // =, >, <, <>, !=, like, not like, <=, >=
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '=',
                'v' => 'Beatles',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '>',
                'v' => 'KISS',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<',
                'v' => 'Cult',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<>',
                'v' => 'Beatles',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '!=',
                'v' => 'Beatles',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'v' => 'Beat%',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'v' => '%Stones',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'v' => '%s%',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Beatles',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'not like',
                'v' => '%s%',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => '>=',
                'v' => '3',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_can_filter_on_null_values()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'null',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
        ], $actual);
    }

    /** @test */
    public function it_can_filter_on_non_null_values()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'not_null',
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_an_IN_filter()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'in',
                'v' => [1, 3],
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Beatles',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_NOT_IN_filter()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'not_in',
                'v' => [1, 3],
            ],
        ])->getModels()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_defaults_to_an_EQUALS_check_if_no_operator_is_given()
    {
        $actual = Band::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'v' => 'Beatles',
            ],
        ])->getModels()->pluck('name')->toArray();

        $this->assertEquals([
            'Beatles',
        ], $actual);
    }
}
