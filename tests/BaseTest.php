<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Facades\Jory as Facade;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Models\SubFolder\Album;

class BaseTest extends TestCase
{

    /** @test */
    public function it_can_apply_a_jory_json_string()
    {
        $actual = Facade::onModelClass(Song::class)
            ->applyJson('{"filter":{"f":"title","o":"like","d":"%love"},"fld":["title"]}')
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'May This Be Love'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_jory_array()
    {
        $actual = Facade::onModelClass(Song::class)->applyArray([
            'filter' => [
                'f' => 'title',
                'o' => 'like',
                'd' => 'love%',
            ],
            'fld' => ['title']
        ])->toArray();

        $this->assertEquals([
            ['title' => 'Love In Vain (Robert Johnson)'],
            ['title' => 'Lovely Rita'],
            ['title' => 'Love or Confusion'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_jory_json_string_from_a_request()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","d":"%zep%"}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_custom_filter()
    {
        $actual = Facade::onModelClass(Album::class)->applyArray([
            'filter' => [
                'f' => 'number_of_songs',
                'o' => '>',
                'd' => 10,
            ],
            'fld' => ['name'],
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Exile on main st.'],
            ['name' => 'Sgt. Peppers lonely hearts club band'],
            ['name' => 'Abbey road'],
            ['name' => 'Let it be'],
            ['name' => 'Are you experienced'],
            ['name' => 'Axis: Bold as love'],
            ['name' => 'Electric ladyland'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_mulitple_custom_filters()
    {
        $actual = Facade::onModelClass(Album::class)->applyArray([
            'filter' => [
                'group_or' => [
                    [
                        'f' => 'number_of_songs',
                        'o' => '>=',
                        'd' => 11,
                    ],
                    [
                        'f' => 'number_of_songs',
                        'o' => '<=',
                        'd' => 9,
                    ],
                ],
            ],
            'fld' => ['name']
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Let it bleed'],
            ['name' => 'Exile on main st.'],
            ['name' => 'Led Zeppelin'],
            ['name' => 'Led Zeppelin II'],
            ['name' => 'Sgt. Peppers lonely hearts club band'],
            ['name' => 'Abbey road'],
            ['name' => 'Let it be'],
            ['name' => 'Are you experienced'],
            ['name' => 'Axis: Bold as love'],
            ['name' => 'Electric ladyland'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_combine_standard_and_custom_filters()
    {
        $actual = Facade::onModelClass(Album::class)->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'f' => 'number_of_songs',
                        'o' => '>=',
                        'd' => 11,
                    ],
                    [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%el%',
                    ],
                ],
            ],
            'fld' => ['name']
        ])->toArray();

        $this->assertEquals([
            ['name' => 'Sgt. Peppers lonely hearts club band'],
            ['name' => 'Electric ladyland'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_override_the_basic_filter_function()
    {
        $actual = Facade::onModelClass(Instrument::class)
            ->applyArray([
                'filter' => [
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

    /** @test */
    public function it_can_return_a_single_model()
    {
        $actual = Facade::onModelClass(Instrument::class)
            ->applyArray([])
            ->first()
            ->toArray();

        $this->assertEquals([
            'id' => 1,
            'name' => 'Vocals',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
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

    /** @test */
    public function it_can_filter_sort_and_select_on_an_ambiguous_column_when_using_a_belongs_to_many_relation()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"id","o":">","d":2},"srt":["-id"],"fld":["id","name"],"rlt":{"people":{"filter":{"f":"id","o":"<","d":14},"srt":["-id"],"fld":["id","last_name"]}}}',
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
