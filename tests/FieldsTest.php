<?php

namespace JosKolenberg\LaravelJory\Tests;

class FieldsTest extends TestCase
{
    /** @test */
    public function it_can_specify_the_fields_to_return()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => [
                    'id',
                    'name',
                ],
            ]
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
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
    public function it_can_specify_the_fields_to_return_on_a_relation()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => ['id', 'name'],
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%zep%',
                ],
                'rlt' => [
                    'songs' => [
                        'fld' => 'title',
                    ]
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'songs' => [
                        [
                            'title' => 'Good Times Bad Times',
                        ],
                        [
                            'title' => 'Babe I\'m Gonna Leave You',
                        ],
                        [
                            'title' => 'You Shook Me',
                        ],
                        [
                            'title' => 'Dazed and Confused',
                        ],
                        [
                            'title' => 'Your Time Is Gonna Come',
                        ],
                        [
                            'title' => 'Black Mountain Side',
                        ],
                        [
                            'title' => 'Communication Breakdown',
                        ],
                        [
                            'title' => 'I Can\'t Quit You Baby',
                        ],
                        [
                            'title' => 'How Many More Times',
                        ],
                        [
                            'title' => 'Whole Lotta Love',
                        ],
                        [
                            'title' => 'What Is and What Should Never Be',
                        ],
                        [
                            'title' => 'The Lemon Song',
                        ],
                        [
                            'title' => 'Thank You',
                        ],
                        [
                            'title' => 'Heartbreaker',
                        ],
                        [
                            'title' => 'Living Loving Maid (She\'s Just A Woman)',
                        ],
                        [
                            'title' => 'Ramble On',
                        ],
                        [
                            'title' => 'Moby Dick',
                        ],
                        [
                            'title' => 'Bring It On Home',
                        ],
                        [
                            'title' => 'Immigrant Song',
                        ],
                        [
                            'title' => 'Friends',
                        ],
                        [
                            'title' => 'Celebration Day',
                        ],
                        [
                            'title' => 'Since I\'ve Been Loving You',
                        ],
                        [
                            'title' => 'Out on the Tiles',
                        ],
                        [
                            'title' => 'Gallows Pole',
                        ],
                        [
                            'title' => 'Tangerine',
                        ],
                        [
                            'title' => 'That\'s the Way',
                        ],
                        [
                            'title' => 'Bron-Y-Aur Stomp',
                        ],
                        [
                            'title' => 'Hats Off to (Roy) Harper',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function when_the_fields_parameter_is_an_empty_array_no_fields_will_be_returned()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => [],
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [],
                [],
                [],
                [],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function when_the_fields_parameter_is_an_empty_array_no_fields_will_be_returned_2()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => [],
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%zep%',
                ],
                'rlt' => [
                    'songs' => [
                        'fld' => 'title',
                        'flt' => [
                            'f' => 'id',
                            'o' => '>',
                            'd' => 54,
                        ]
                    ]
                ]
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'songs' => [
                        [
                            'title' => 'Bring It On Home',
                        ],
                        [
                            'title' => 'Immigrant Song',
                        ],
                        [
                            'title' => 'Friends',
                        ],
                        [
                            'title' => 'Celebration Day',
                        ],
                        [
                            'title' => 'Since I\'ve Been Loving You',
                        ],
                        [
                            'title' => 'Out on the Tiles',
                        ],
                        [
                            'title' => 'Gallows Pole',
                        ],
                        [
                            'title' => 'Tangerine',
                        ],
                        [
                            'title' => 'That\'s the Way',
                        ],
                        [
                            'title' => 'Bron-Y-Aur Stomp',
                        ],
                        [
                            'title' => 'Hats Off to (Roy) Harper',
                        ],
                    ],
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_custom_model_attributes()
    {
        $response = $this->json('GET', 'jory/person', [
            'jory' => [
                'fld' => 'full_name',
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'full_name' => 'Mick Jagger',
                ],
                [
                    'full_name' => 'Keith Richards',
                ],
                [
                    'full_name' => 'Ronnie Wood',
                ],
                [
                    'full_name' => 'Charlie Watts',
                ],
                [
                    'full_name' => 'Robert Plant',
                ],
                [
                    'full_name' => 'Jimmy Page',
                ],
                [
                    'full_name' => 'John Paul Jones',
                ],
                [
                    'full_name' => 'John Bonham',
                ],
                [
                    'full_name' => 'John Lennon',
                ],
                [
                    'full_name' => 'Paul McCartney',
                ],
                [
                    'full_name' => 'George Harrison',
                ],
                [
                    'full_name' => 'Ringo Starr',
                ],
                [
                    'full_name' => 'Jimi Hendrix',
                ],
                [
                    'full_name' => 'Noel Redding',
                ],
                [
                    'full_name' => 'Mitch Mitchell',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_use_an_asterisk_to_select_all_fields_configured_in_the_jory_resource()
    {
        $response = $this->json('GET', 'jory/person/1', [
            'jory' => [
                'fld' => '*',
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'id' => 1,
                'first_name' => 'Mick',
                'last_name' => 'Jagger',
                'date_of_birth' => '1943/07/26',
                'full_name' => 'Mick Jagger',
                'instruments_string' => 'Vocals',
                'first_image_url' => 'peron_image_1.jpg',
            ],
        ]);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_can_use_an_asterisk_in_array_notation_to_select_all_fields_configured_in_the_jory_resource()
    {
        $response = $this->json('GET', 'jory/person/1', [
            'jory' => [
                'fld' => ['*'],
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'id' => 1,
                'first_name' => 'Mick',
                'last_name' => 'Jagger',
                'date_of_birth' => '1943/07/26',
                'full_name' => 'Mick Jagger',
                'instruments_string' => 'Vocals',
                'first_image_url' => 'peron_image_1.jpg',
            ],
        ]);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_returns_no_fields_when_the_fields_parameter_is_omitted()
    {
        $response = $this->json('GET', 'jory/person/1', [
            'jory' => [],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_returns_no_fields_when_the_fields_parameter_is_an_empty_array()
    {
        $response = $this->json('GET', 'jory/person/1', [
            'jory' => [],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [],
        ]);

        $this->assertQueryCount(1);
    }
}
