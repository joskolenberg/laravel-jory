<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Tests\Controllers\SongWithBlueprintController;

class JoryBuilderWithBlueprintTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Route::get('song', SongWithBlueprintController::class.'@index');
        Route::get('song-two', SongWithBlueprintController::class.'@indexTwo');
        Route::get('song-three', SongWithBlueprintController::class.'@indexThree');
        Route::options('song', SongWithBlueprintController::class.'@options');
        Route::options('song-two', SongWithBlueprintController::class.'@optionsTwo');
        Route::options('song-three', SongWithBlueprintController::class.'@optionsThree');
    }

    /** @test */
    public function it_can_validate_if_a_requested_field_is_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":5}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'Gimme Shelter',
                ],
                [
                    'title' => 'Love In Vain (Robert Johnson)',
                ],
                [
                    'title' => 'Country Honk',
                ],
                [
                    'title' => 'Live With Me',
                ],
                [
                    'title' => 'Let It Bleed',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_a_requested_field_is_not_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["titel"],"lmt":5}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" not available. Did you mean "title"? (Location: fields.0)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource()
    {
        $response = $this->json('OPTIONS', 'song');

        $expected = [
            'fields' => [
                'id' => [
                    'description' => 'Not defined.',
                    'show_by_default' => true,
                ],
                'title' => [
                    'description' => 'The songs title.',
                    'show_by_default' => true,
                ],
                'album_id' => [
                    'description' => 'Not defined.',
                    'show_by_default' => false,
                ],
            ],
            'filters' => [
                'title' => [
                    'description' => 'Filter on the title.',
                    'operators' => [
                        '=',
                        '!=',
                        '<>',
                        '>',
                        '>=',
                        '<',
                        '<=',
                        '<=>',
                        'like',
                        'null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'album_id' => [
                    'description' => 'Filter on the album id.',
                    'operators' => [
                        '=',
                    ],
                ],
            ],
            'sorts' => [
                'title' => [
                    'description' => 'Order by the title.',
                ],
                'id' => [
                    'description' => 'Not defined.',
                ],
            ],
            'limit' => [
                'default' => 50,
                'max' => 250,
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource_2()
    {
        $response = $this->json('OPTIONS', 'song-two');

        $expected = [
            'fields' => 'Not defined.',
            'filters' => 'Not defined.',
            'sorts' => 'Not defined.',
            'limit' => [
                'default' => 'Unlimited.',
                'max' => 'Unlimited.',
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource_3()
    {
        $response = $this->json('OPTIONS', 'song-three');

        $expected = [
            'fields' => 'Not defined.',
            'filters' => 'Not defined.',
            'sorts' => 'Not defined.',
            'limit' => [
                'default' => 10,
                'max' => 10,
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_load_the_default_fields_when_no_fields_are_specified_in_the_request()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"srt":["-id"],"lmt":3}',
        ]);

        $expected = [
            'data' => [
                [
                    'id' => 147,
                    'title' => 'Voodoo Child (Slight Return)',
                ],
                [
                    'id' => 146,
                    'title' => 'All Along the Watchtower',
                ],
                [
                    'id' => 145,
                    'title' => 'House Burning Down',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_a_requested_filter_is_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"f":"title","o":"like","v":"%love%"}}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'Love In Vain (Robert Johnson)',
                ],
                [
                    'title' => 'Whole Lotta Love',
                ],
                [
                    'title' => 'Lovely Rita',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_a_requested_filter_is_not_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"f":"titel","o":"like","v":"%love%"}}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" is not supported for filtering. Did you mean "title"? (Location: filter)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_requested_filters_are_not_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"and":[{"f":"titel","o":"like","v":"%love%"},{"f":"title","o":"like","v":"%test%"},{"f":"albumm_id","v":11}]}}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" is not supported for filtering. Did you mean "title"? (Location: filter(and).0)',
                'Field "albumm_id" is not supported for filtering. Did you mean "album_id"? (Location: filter(and).2)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_the_requested_operator_is_available_on_a_filter()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"and":[{"f":"titel","o":"like","v":"%love%"},{"f":"title","o":"like","v":"%test%"},{"f":"albumm_id","v":11},{"f":"album_id","o":"like","v":11}]}}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" is not supported for filtering. Did you mean "title"? (Location: filter(and).0)',
                'Field "albumm_id" is not supported for filtering. Did you mean "album_id"? (Location: filter(and).2)',
                'Operator "like" is not supported by field "album_id". (Location: filter(and).3.album_id)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_a_requested_sort_is_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"f":"title","o":"like","v":"%love%"},"srt":["title"]}',
        ]);

        $expected = [
            'data' => [
                [
                    "title" => "And the Gods Made Love",
                ],
                [
                    "title" => "Bold as Love",
                ],
                [
                    "title" => "Little Miss Lover",
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_a_requested_sort_is_not_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"f":"title","o":"like","v":"%love%"},"srt":["tite","if"]}',
        ]);

        $expected = [
            'errors' => [
                'Field "tite" is not supported for sorting. Did you mean "title"? (Location: sorts.0)',
                'Field "if" is not supported for sorting. Did you mean "id"? (Location: sorts.1)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_the_requested_limit_exceeds_the_maximum()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"flt":{"f":"title","o":"like","v":"%love%"},"lmt":1500}',
        ]);

        $expected = [
            'errors' => [
                'The maximum limit for this resource is 250, please lower your limit or drop the limit parameter. (Location: limit)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_validate_if_the_requested_limit_does_not_exceed_the_maximum()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"flt":{"f":"title","o":"like","v":"%love"},"srt":["title"],"lmt":250}',
        ]);

        $expected = [
            'data' => [
                [
                    "title" => "And the Gods Made Love",
                ],
                [
                    "title" => "Bold as Love",
                ],
                [
                    "title" => "May This Be Love",
                ],
                [
                    "title" => "Whole Lotta Love",
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_apply_the_default_limit_when_no_limit_is_given()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"]}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'Gimme Shelter',
                ],
                [
                    'title' => 'Love In Vain (Robert Johnson)',
                ],
                [
                    'title' => 'Country Honk',
                ],
                [
                    'title' => 'Live With Me',
                ],
                [
                    'title' => 'Let It Bleed',
                ],
                [
                    'title' => 'Midnight Rambler',
                ],
                [
                    'title' => 'You Got The Silver',
                ],
                [
                    'title' => 'Monkey Man',
                ],
                [
                    'title' => 'You Can\'t Always Get What You Want',
                ],
                [
                    'title' => 'Brown Sugar',
                ],
                [
                    'title' => 'Sway',
                ],
                [
                    'title' => 'Wild Horses',
                ],
                [
                    'title' => 'Can\'t You Hear Me Knocking',
                ],
                [
                    'title' => 'You Gotta Move',
                ],
                [
                    'title' => 'Bitch',
                ],
                [
                    'title' => 'I Got The Blues',
                ],
                [
                    'title' => 'Sister Morphine',
                ],
                [
                    'title' => 'Dead Flowers',
                ],
                [
                    'title' => 'Moonlight Mile',
                ],
                [
                    'title' => 'Rocks Off',
                ],
                [
                    'title' => 'Rip This Joint',
                ],
                [
                    'title' => 'Shake Your Hips',
                ],
                [
                    'title' => 'Casino Boogie',
                ],
                [
                    'title' => 'Tumbling Dice',
                ],
                [
                    'title' => 'Sweet Virginia',
                ],
                [
                    'title' => 'Torn and Frayed',
                ],
                [
                    'title' => 'Sweet Black Angel',
                ],
                [
                    'title' => 'Loving Cup',
                ],
                [
                    'title' => 'Happy',
                ],
                [
                    'title' => 'Turd on the Run',
                ],
                [
                    'title' => 'Ventilator Blues',
                ],
                [
                    'title' => 'I Just Want to See His Face',
                ],
                [
                    'title' => 'Let It Loose',
                ],
                [
                    'title' => 'All Down the Line',
                ],
                [
                    'title' => 'Stop Breaking Down',
                ],
                [
                    'title' => 'Shine a Light',
                ],
                [
                    'title' => 'Soul Survivor',
                ],
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
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_does_not_validate_on_limit_when_the_max_is_set_to_null()
    {
        $response = $this->json('GET', 'song-two', [
            'jory' => '{"fld":["title"],"flt":{"f":"title","o":"like","v":"%love"},"srt":["title"],"lmt":321311}',
        ]);

        $expected = [
            'data' => [
                [
                    "title" => "And the Gods Made Love",
                ],
                [
                    "title" => "Bold as Love",
                ],
                [
                    "title" => "May This Be Love",
                ],
                [
                    "title" => "Whole Lotta Love",
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_applies_the_max_limit_as_default_when_only_the_max_is_given()
    {
        $response = $this->json('GET', 'song-three', [
            'jory' => '{"fld":["title"]}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'Gimme Shelter',
                ],
                [
                    'title' => 'Love In Vain (Robert Johnson)',
                ],
                [
                    'title' => 'Country Honk',
                ],
                [
                    'title' => 'Live With Me',
                ],
                [
                    'title' => 'Let It Bleed',
                ],
                [
                    'title' => 'Midnight Rambler',
                ],
                [
                    'title' => 'You Got The Silver',
                ],
                [
                    'title' => 'Monkey Man',
                ],
                [
                    'title' => 'You Can\'t Always Get What You Want',
                ],
                [
                    'title' => 'Brown Sugar',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }
}