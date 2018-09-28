<?php

namespace JosKolenberg\LaravelJory\Tests;


use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;

class CustomJoryBuilderTest extends TestCase
{

    /** @test */
    function it_can_apply_a_custom_filter()
    {
        $actual = Album::jory()
            ->applyArray([
                'filter' => [
                    'f' => 'number_of_songs',
                    'o' => '>',
                    'v' => 10,
                ]
            ])
            ->get()
            ->pluck('name')
            ->toArray();

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
    function it_can_apply_mulitple_custom_filters()
    {
        $actual = Album::jory()
            ->applyArray([
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
                ]
            ])
            ->get()
            ->pluck('name')
            ->toArray();

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
    function it_can_override_the_basic_filter_function()
    {
        $actual = Instrument::jory()
            ->applyArray([
                'filter' => [
                    'f' => 'name',
                    'o' => 'like',
                    'v' => '%t%',
                ],
            ])
            ->get()
            ->pluck('name')
            ->toArray();

        $this->assertEquals([
            'Guitar',
            'Bassguitar',
            // An extra custom filter is made to exclude instruments without connected people, so flute should be missing
        ], $actual);
    }

}