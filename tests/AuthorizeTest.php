<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\User;

class AuthorizeTest extends TestCase
{
    /** @test */
    public function it_can_modify_the_query_by_authorize_method_1()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"srt":["name"]}',
        ]);

        $expected = [
            'data' => [
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_modify_the_query_by_authorize_method_2()
    {
        $this->actingAs(User::where('name', 'mick')->first());

        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"srt":["name"]}',
        ]);

        $expected = [
            'data' => [
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_modify_the_query_by_authorize_method_3()
    {
        $this->actingAs(User::where('name', 'ronnie')->first());

        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"srt":["name"]}',
        ]);

        $expected = [
            'data' => [
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_modify_the_query_by_authorize_method_in_relations()
    {
        $this->actingAs(User::where('name', 'mick')->first());

        $response = $this->json('GET', 'jory/album', [
            'jory' => '{"flt":{"f":"id","o":"in","d":[2,9]},"fld":["name"],"rlt":{"band":{"fld":["name"]}}}',
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Sticky Fingers',
                    'band' => null,
                ],
                [
                    'name' => 'Let it be',
                    'band' => [
                        'name' => 'Beatles',
                    ],
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(3);
    }
}
