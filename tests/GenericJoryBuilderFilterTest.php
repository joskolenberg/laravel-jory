<?php
/**
 * Created by PhpStorm.
 * User: joskolenberg
 * Date: 24-09-18
 * Time: 11:43.
 */

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\WithTraits\BandWithTrait;
use JosKolenberg\LaravelJory\Tests\Models\WithTraits\PersonWithTrait;
use JosKolenberg\LaravelJory\Tests\Models\WithTraits\SongWithTrait;

class GenericJoryBuilderFilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_apply_a_single_filter()
    {
        $actual = PersonWithTrait::jory()->applyArray([
            'filter' => [
                'field'    => 'first_name',
                'operator' => 'like',
                'value'    => '%john%',
            ],
        ])->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Jones', 'Bonham', 'Lennon'], $actual);
    }

    /**
     * @test
     */
    public function it_can_apply_an_OR_filter_group()
    {
        $actual = PersonWithTrait::jory()->applyArray([
            'filter' => [
                'group_or' => [
                    [
                        'field'    => 'first_name',
                        'operator' => 'like',
                        'value'    => '%paul%',
                    ],
                    [
                        'field'    => 'last_name',
                        'operator' => 'like',
                        'value'    => '%le%',
                    ],
                ],
            ],
        ])->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Jones', 'Lennon', 'McCartney'], $actual);
    }

    /**
     * @test
     */
    public function it_can_apply_an_AND_filter_group()
    {
        $actual = PersonWithTrait::jory()->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field'    => 'first_name',
                        'operator' => 'like',
                        'value'    => '%john%',
                    ],
                    [
                        'field'    => 'last_name',
                        'operator' => 'like',
                        'value'    => '%le%',
                    ],
                ],
            ],
        ])->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Lennon'], $actual);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_1()
    {
        $actual = SongWithTrait::jory()->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field'    => 'name',
                        'operator' => 'like',
                        'value'    => '%love%',
                    ],
                ],
            ],
        ])->get()->pluck('name')->toArray();

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
        $actual = SongWithTrait::jory()->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field'    => 'name',
                        'operator' => 'like',
                        'value'    => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field'    => 'name',
                                'operator' => 'like',
                                'value'    => '%bold%',
                            ],
                            [
                                'field'    => 'name',
                                'operator' => 'like',
                                'value'    => '%er%',
                            ],
                        ],
                    ],
                ],
            ],
        ])->get()->pluck('name')->toArray();

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
        $actual = SongWithTrait::jory()->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field'    => 'name',
                        'operator' => 'like',
                        'value'    => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field'    => 'name',
                                'operator' => 'like',
                                'value'    => '%bold%',
                            ],
                            [
                                'field'    => 'name',
                                'operator' => 'like',
                                'value'    => '%er%',
                            ],
                            [
                                'group_and' => [
                                    [
                                        'field'    => 'name',
                                        'operator' => 'like',
                                        'value'    => 'may%',
                                    ],
                                    [
                                        'field'    => 'name',
                                        'operator' => 'like',
                                        'value'    => '%love',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])->get()->pluck('name')->toArray();

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
        $actual = SongWithTrait::jory()->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field'    => 'name',
                        'operator' => 'like',
                        'value'    => '%love%',
                    ],
                    [
                        'group_or' => [
                            [
                                'field'    => 'name',
                                'operator' => 'like',
                                'value'    => '%bold%',
                            ],
                            [
                                'field'    => 'name',
                                'operator' => 'like',
                                'value'    => '%er%',
                            ],
                            [
                                'group_and' => [
                                    [
                                        'field'    => 'name',
                                        'operator' => 'like',
                                        'value'    => '%e%',
                                    ],
                                    [
                                        'field'    => 'name',
                                        'operator' => 'like',
                                        'value'    => '%a%',
                                    ],
                                    [
                                        'group_or' => [
                                            [
                                                'field'    => 'name',
                                                'operator' => 'like',
                                                'value'    => '%whole%',
                                            ],
                                            [
                                                'field'    => 'name',
                                                'operator' => 'like',
                                                'value'    => '%gods%',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])->get()->pluck('name')->toArray();

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
        $actual = BandWithTrait::jory()->applyArray([])->get()->pluck('name')->toArray();

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
        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '=',
                'v' => 'Beatles',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '>',
                'v' => 'KISS',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<',
                'v' => 'Cult',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<>',
                'v' => 'Beatles',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '!=',
                'v' => 'Beatles',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'v' => 'Beat%',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'v' => '%Stones',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'v' => '%s%',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Beatles',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'not like',
                'v' => '%s%',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => '>=',
                'v' => '3',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_can_filter_on_null_values()
    {
        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'null',
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
        ], $actual);
    }

    /** @test */
    public function it_can_filter_on_non_null_values()
    {
        $actual = BandWithTrait::jory()->applyArray([
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
    }

    /** @test */
    public function it_can_apply_an_IN_filter()
    {
        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'in',
                'v' => [1, 3],
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Beatles',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_NOT_IN_filter()
    {
        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'not_in',
                'v' => [1, 3],
            ],
        ])->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_defaults_to_an_EQUALS_check_if_no_operator_is_given()
    {
        $actual = BandWithTrait::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'v' => 'Beatles',
            ],
        ])->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Beatles',
        ], $actual);
    }
}
