<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\User;

class AuthorizeTest extends TestCase
{
    /** @test */
    public function it_can_modify_the_query_by_authorize_method_1()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => 'name',
                'srt' => 'name',
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Beatles',
                ],
                [
                    'name' => 'Jimi Hendrix Experience',
                ],
                [
                    'name' => 'Led Zeppelin',
                ],
                [
                    'name' => 'Rolling Stones',
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
            'jory' => [
                'fld' => 'name',
                'srt' => 'name',
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Beatles',
                ],
                [
                    'name' => 'Jimi Hendrix Experience',
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
            'jory' => [
                'fld' => 'name',
                'srt' => 'name',
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Beatles',
                ],
                [
                    'name' => 'Jimi Hendrix Experience',
                ],
                [
                    'name' => 'Led Zeppelin',
                ],
                [
                    'name' => 'Rolling Stones',
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
            'jory' => [
                'flt' => [
                    'f' => 'id',
                    'o' => 'in',
                    'd' => [2,9],
                ],
                'fld' => 'name',
                'rlt' => [
                    'band' => [
                        'fld' => 'name',
                    ]
                ]
            ],
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

    /** @test */
    public function the_authorize_method_is_scoped()
    {
        $this->actingAs(User::where('name', 'keith')->first());

        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%t%'
                ],
                'fld' => 'name',
                'srt' => 'name',
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Rolling Stones',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }
}
