<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\Band;

class GenericJoryBuilderFieldsTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();

        Band::joryRoutes('band');
    }

    /** @test */
    public function it_can_specify_the_fields_to_return()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"fields":["id","name"]}',
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
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
            ]);
    }

    /** @test */
    public function it_can_specify_the_fields_to_return_on_a_relation()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"flt":{"f":"name","o":"like","v":"%zep%"},"rlt":{"songs":{"fld":["title"]}},"fields":["id","name"]}',
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
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
                    ]
                ],
            ]);
    }

    /** @test */
    public function when_the_fields_parameter_is_not_specified_all_fields_will_be_returned()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{}',
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
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
            ]);
    }

    /** @test */
    public function when_the_fields_parameter_is_an_empty_array_no_fields_will_be_returned()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"fld":[]}',
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
                [
                ],
                [
                ],
                [
                ],
                [
                ],
            ]);
    }

    /** @test */
    public function when_the_fields_parameter_is_an_empty_array_no_fields_will_be_returned_2()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"fld":[],"flt":{"f":"name","o":"like","v":"%zep%"},"rlt":{"songs":{"flt":{"f":"songs.id","o":">","v":54},"fld":["title"]}}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
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
                    ]
                ],
            ]);
    }

}
