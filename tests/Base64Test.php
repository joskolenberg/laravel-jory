<?php

namespace JosKolenberg\LaravelJory\Tests;

class Base64Test extends TestCase
{

    /** @test */
    public function it_can_process_a_base64_encoded_json_string_1()
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
                        'fld' => [
                            'id',
                            'band_id',
                            'name',
                            'release_date',
                        ],
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
                'fld' => 'name',
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
                                        'album' => [
                                            'fld' => 'name',
                                        ],
                                    ],
                            ],
                    ],
            ])),
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Rolling Stones',
                    'songs' => [
                        [
                            'id' => 2,
                            'title' => 'Love In Vain (Robert Johnson)',
                            'album' => [
                                'name' => 'Let it bleed',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Led Zeppelin',
                    'songs' => [
                        [
                            'id' => 47,
                            'title' => 'Whole Lotta Love',
                            'album' => [
                                'name' => 'Led Zeppelin II',
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
                            'fld' => 'name',
                            'flt' => [
                                'f' => 'name',
                                'o' => 'like',
                                'd' => '%III%',
                            ]
                        ]
                    ]
                ],
                'song as songs' => [
                    'fld' => 'title',
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
                            'name' => 'Led Zeppelin III',
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
