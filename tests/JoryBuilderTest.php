<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Facades\Jory as Facade;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\BandJoryBuilder;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\InstrumentJoryBuilder;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class JoryBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_apply_on_a_querybuilder_instance()
    {
        $query = Band::query();
        $actual = (new BandJoryBuilder())->applyJory(new Jory())->onQuery($query)->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_jory_json_string()
    {
        $actual = Facade::byModel(Song::class)
            ->applyJson('{"filter":{"f":"title","o":"like","d":"%love"}}')
            ->getProcessedBuilder()
            ->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Whole Lotta Love',
            'May This Be Love',
            'Bold as Love',
            'And the Gods Made Love',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_jory_array()
    {
        $actual = Facade::byModel(Song::class)->applyArray([
            'filter' => [
                'f' => 'title',
                'o' => 'like',
                'd' => 'love%',
            ],
        ])->getProcessedBuilder()->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Lovely Rita',
            'Love or Confusion',
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
        $actual = Facade::byModel(Album::class)->applyArray([
            'filter' => [
                'f' => 'number_of_songs',
                'o' => '>',
                'd' => 10,
            ],
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Exile on main st.',
            'Sgt. Peppers lonely hearts club band',
            'Abbey road',
            'Let it be',
            'Are you experienced',
            'Axis: Bold as love',
            'Electric ladyland',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_mulitple_custom_filters()
    {
        $actual = Facade::byModel(Album::class)->applyArray([
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
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Let it bleed',
            'Exile on main st.',
            'Led Zeppelin',
            'Led Zeppelin II',
            'Sgt. Peppers lonely hearts club band',
            'Abbey road',
            'Let it be',
            'Are you experienced',
            'Axis: Bold as love',
            'Electric ladyland',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_combine_standard_and_custom_filters()
    {
        $actual = Facade::byModel(Album::class)->applyArray([
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
        ])->getProcessedBuilder()->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Sgt. Peppers lonely hearts club band',
            'Electric ladyland',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_override_the_basic_filter_function()
    {
        $actual = \JosKolenberg\LaravelJory\Facades\Jory::byModel(Instrument::class)
            ->applyArray([
                'filter' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%t%',
                ],
            ])->getProcessedBuilder()->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Guitar',
            'Bassguitar',
            // An extra custom filter is made to exclude instruments without connected people, so flute should be missing
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_a_single_model()
    {
        $actual = (new InstrumentJoryBuilder())
            ->onQuery(Instrument::query())
            ->applyJory(new Jory())
            ->firstToArray();

        $this->assertEquals([
            'id' => 1,
            'name' => 'Vocals',
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_returns_null_when_a_single_model_is_not_found()
    {
        $actual = \JosKolenberg\LaravelJory\Facades\Jory::byModel(Instrument::class)
            ->applyArray([
                'flt' => [
                    'f' => 'name',
                    'd' => 'Hobo',
                ],
            ])->getProcessedBuilder()
            ->firstToArray();

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
