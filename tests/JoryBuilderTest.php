<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;

class JoryBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_apply_on_a_querybuilder_instance()
    {
        $query = Band::query();
        $actual = (new JoryBuilder())->onQuery($query)->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_json_string()
    {
        $actual = Song::jory()->applyJson('{"filter":{"f":"title","o":"like","v":"%love"}}')->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Whole Lotta Love',
            'May This Be Love',
            'Bold as Love',
            'And the Gods Made Love',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_array()
    {
        $actual = Song::jory()->applyArray([
            'filter' => [
                'f' => 'title',
                'o' => 'like',
                'v' => 'love%',
            ],
        ])->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Lovely Rita',
            'Love or Confusion',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_json_string_from_a_request()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","v":"%zep%"}}',
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
    }

    /** @test */
    public function it_can_apply_a_jory_object()
    {
        $jory = (new ArrayParser([
            'filter' => [
                'f' => 'title',
                'o' => 'like',
                'v' => 'love%',
            ],
        ]))->getJory();

        $actual = Song::jory()->applyJory($jory)->get()->pluck('title')->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Lovely Rita',
            'Love or Confusion',
        ], $actual);
    }

    /** @test */
    public function it_defaults_to_empty_when_no_jory_is_applied()
    {
        $actual = Band::jory()->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_custom_filter()
    {
        $actual = Album::jory()->applyArray([
            'filter' => [
                'f' => 'number_of_songs',
                'o' => '>',
                'v' => 10,
            ],
        ])->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Exile on main st.',
            'Sgt. Peppers lonely hearts club band',
            'Abbey road',
            'Let it be',
            'Are you experienced',
            'Axis: Bold as love',
            'Electric ladyland',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_mulitple_custom_filters()
    {
        $actual = Album::jory()->applyArray([
            'filter' => [
                'group_or' => [
                    [
                        'f' => 'number_of_songs',
                        'o' => '>=',
                        'v' => 11,
                    ],
                    [
                        'f' => 'number_of_songs',
                        'o' => '<=',
                        'v' => 9,
                    ],
                ],
            ],
        ])->get()->pluck('name')->toArray();

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
    }

    /** @test */
    public function it_can_combine_standard_and_custom_filters()
    {
        $actual = Album::jory()->applyArray([
            'filter' => [
                'group_and' => [
                    [
                        'f' => 'number_of_songs',
                        'o' => '>=',
                        'v' => 11,
                    ],
                    [
                        'f' => 'name',
                        'o' => 'like',
                        'v' => '%el%',
                    ],
                ],
            ],
        ])->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Sgt. Peppers lonely hearts club band',
            'Electric ladyland',
        ], $actual);
    }

    /** @test */
    public function it_can_override_the_basic_filter_function()
    {
        $actual = Instrument::jory()->applyArray([
            'filter' => [
                'f' => 'name',
                'o' => 'like',
                'v' => '%t%',
            ],
        ])->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Guitar',
            'Bassguitar',
            // An extra custom filter is made to exclude instruments without connected people, so flute should be missing
        ], $actual);
    }

    /** @test */
    public function it_can_return_a_single_model()
    {
        $actual = Instrument::jory()->first()->toArray();

        $this->assertEquals([
            'id' => 1,
            'name' => 'Vocals',
        ], $actual);
    }

    /** @test */
    public function it_returns_null_when_a_single_model_is_not_found()
    {
        $actual = Instrument::jory()->applyArray([
            'flt' => [
                'f' => 'name',
                'v' => 'Hobo',
            ],
        ])->first()->toArray();

        $this->assertNull($actual);
    }
}
