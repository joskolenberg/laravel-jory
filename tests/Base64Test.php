<?php

namespace JosKolenberg\LaravelJory\Tests;

class Base64Test extends TestCase
{

    /** @test */
    public function it_can_process_a_base64_encoded_json_string()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => base64_encode(json_encode([
                'fld' => [
                    'id',
                    'name',
                ],
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%zep%',
                ],
                'rlt' => [
                    'albums' => [
                        'flt' => [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%III%',
                        ]
                    ]
                ]
            ])),
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'albums' => [
                        [
                            'id' => 6,
                            'band_id' => 2,
                            'name' => 'Led Zeppelin III',
                            'release_date' => '1970-10-05 00:00:00',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_process_a_base64_encoded_json_string_2()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => base64_encode(json_encode([
                'filter' =>
                    [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%in%',
                    ],
                'rlt' =>
                    [
                        'songs' =>
                            [
                                'fld' =>
                                    [
                                        'id',
                                        'title',
                                    ],
                                'flt' =>
                                    [
                                        'f' => 'title',
                                        'o' => 'like',
                                        'd' => '%love%',
                                    ],
                                'rlt' =>
                                    [
                                        'album' => [],
                                    ],
                            ],
                    ],
            ])),
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                    'songs' => [
                        [
                            'id' => 2,
                            'title' => 'Love In Vain (Robert Johnson)',
                            'album' => [
                                'id' => 1,
                                'band_id' => 1,
                                'name' => 'Let it bleed',
                                'release_date' => '1969-12-05 00:00:00',
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                    'songs' => [
                        [
                            'id' => 47,
                            'title' => 'Whole Lotta Love',
                            'album' => [
                                'id' => 5,
                                'band_id' => 2,
                                'name' => 'Led Zeppelin II',
                                'release_date' => '1969-10-22 00:00:00',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_can_process_a_base64_encoded_json_string_for_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => base64_encode(json_encode([
                'band:first as lz' => [
                    'fld' => [
                        'id',
                        'name',
                    ],
                    'flt' => [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%zep%',
                    ],
                    'rlt' => [
                        'albums' => [
                            'flt' => [
                                'f' => 'name',
                                'o' => 'like',
                                'd' => '%III%',
                            ]
                        ]
                    ]
                ],
                'song as songs' => [
                    'fld' => [
                        'title',
                    ],
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%let%',
                    ],
                ]
            ])),
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'lz' => [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'albums' => [
                        [
                            'id' => 6,
                            'band_id' => 2,
                            'name' => 'Led Zeppelin III',
                            'release_date' => '1970-10-05 00:00:00',
                        ],
                    ],
                ],
                'songs' => [
                    [
                        'title' => 'Let It Be',
                    ],
                    [
                        'title' => 'Let It Bleed',
                    ],
                    [
                        'title' => 'Let It Loose',
                    ],
                ],
            ],
        ]);

        $this->assertQueryCount(3);
    }
}
