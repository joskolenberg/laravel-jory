<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Facades\Jory;
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
        $actual = Jory::byModel(Person::class)->applyArray([
            'filter' => [
                'field' => 'first_name',
                'operator' => 'like',
                'data' => '%john%',
            ],
        ])->getProcessedBuilder()->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Jones', 'Bonham', 'Lennon'], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_an_OR_filter_group()
    {
        $actual = Jory::byModel(Person::class)->applyArray([
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
        ])->getProcessedBuilder()->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Jones', 'Lennon', 'McCartney'], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_an_AND_filter_group()
    {
        $actual = Jory::byModel(Person::class)->applyArray([
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
        ])->getProcessedBuilder()->get()->pluck('last_name')->toArray();

        $this->assertEquals(['Lennon'], $actual);

        $this->assertQueryCount(1);
    }

    /**
     * @test
     */
    public function it_can_apply_nested_filters_1()
    {
        $actual = Jory::byModel(Song::class)->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'field' => 'title',
                        'operator' => 'like',
                        'data' => '%love%',
                    ],
                ],
            ],
        ])->getProcessedBuilder()->get()->pluck('title')->toArray();

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
        $actual = Jory::byModel(Song::class)->applyArray([
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
        ])->getProcessedBuilder()->get()->pluck('title')->toArray();

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
        $actual = Jory::byModel(Song::class)->applyArray([
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
        ])->getProcessedBuilder()->get()->pluck('title')->toArray();

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
        $actual = Jory::byModel(Song::class)->applyArray([
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
        ])->getProcessedBuilder()->get()->pluck('title')->toArray();

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
        $actual = Jory::byModel(Band::class)
            ->applyArray([])->getProcessedBuilder()->get()->pluck('name')->toArray();

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
        // =, >, <, <>, !=, like, not_like, <=, >=
        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '=',
                'd' => 'Beatles',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '>',
                'd' => 'KISS',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<',
                'd' => 'Cult',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '<>',
                'd' => 'Beatles',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => '!=',
                'd' => 'Beatles',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => 'Beat%',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => '%Stones',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'd' => '%s%',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Beatles',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'not_like',
                'd' => '%s%',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => '>=',
                'd' => '3',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);

        $this->assertQueryCount(10);
    }

    /** @test */
    public function it_can_filter_on_null_values()
    {
        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'is_null',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_filter_on_non_null_values()
    {
        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'year_end',
                'o' => 'not_null',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
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
        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'in',
                'd' => [1, 3],
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Rolling Stones',
            'Beatles',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_NOT_IN_filter()
    {
        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'id',
                'o' => 'not_in',
                'd' => [1, 3],
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();
        $this->assertEquals([
            'Led Zeppelin',
            'Jimi Hendrix Experience',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_defaults_to_an_EQUALS_check_if_no_operator_is_given()
    {
        $actual = Jory::byModel(Band::class)->applyArray([
            'filter' => [
                'f' => 'name',
                'd' => 'Beatles',
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();

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
}
