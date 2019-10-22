<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Tests\Controllers\SongWithConfigController;

class WithConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('song', SongWithConfigController::class.'@index')->middleware('jory');
        Route::get('song-two', SongWithConfigController::class.'@indexTwo')->middleware('jory');
        Route::get('song-three', SongWithConfigController::class.'@indexThree')->middleware('jory');
        Route::options('song', SongWithConfigController::class.'@options')->middleware('jory');
        Route::options('song-two', SongWithConfigController::class.'@optionsTwo')->middleware('jory');
        Route::options('song-three', SongWithConfigController::class.'@optionsThree')->middleware('jory');
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

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_validate_if_a_requested_field_is_not_available_2()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["titel"],"lmt":5}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" is not available, did you mean "title"? (Location: fields.titel)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource()
    {
        $response = $this->json('OPTIONS', 'song');

        $expected = [
            'fields' => [
                'id' => [
                    'description' => 'The id field.',
                    'default' => true,
                ],
                'title' => [
                    'description' => 'The songs title.',
                    'default' => true,
                ],
                'album_id' => [
                    'description' => 'The album_id field.',
                    'default' => false,
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
                        'not_like',
                        'is_null',
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
                    'default' => false,
                ],
                'id' => [
                    'description' => 'Sort by the id field.',
                    'default' => false,
                ],
            ],
            'limit' => [
                'default' => 50,
                'max' => 250,
            ],
            'relations' => [
                'album' => [
                    'description' => 'The album relation.',
                    'type' => 'album',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource_2()
    {
        $response = $this->json('OPTIONS', 'song-two');

        $expected = [
            'fields' => [
                'id' => [
                    'description' => 'The id field.',
                    'default' => true,
                ],
                'title' => [
                    'description' => 'The title field.',
                    'default' => true,
                ],
                'album_id' => [
                    'description' => 'The album_id field.',
                    'default' => true,
                ],
            ],
            'filters' => [
                'title' => [
                    'description' => 'Filter on the title field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
            ],
            'sorts' => [
                'title' => [
                    'description' => 'Sort by the title field.',
                    'default' => false,
                ],
            ],
            'limit' => [
                'default' => null,
                'max' => null,
            ],
            'relations' => [],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource_3()
    {
        $response = $this->json('OPTIONS', 'song-three');

        $expected = [
            'fields' => [
                'id' => [
                    'description' => 'The id field.',
                    'default' => true,
                ],
                'title' => [
                    'description' => 'The title field.',
                    'default' => true,
                ],
                'album_id' => [
                    'description' => 'The album_id field.',
                    'default' => true,
                ],
            ],
            'filters' => [],
            'sorts' => [
                'title' => [
                    'description' => 'Sort by the title field.',
                    'default' => [
                        'index' => 2,
                        'order' => 'desc'
                    ],
                ],
                'album_name' => [
                    'description' => 'Sort by the album_name field.',
                    'default' => [
                        'index' => 1,
                        'order' => 'asc'
                    ],
                ],
            ],
            'limit' => [
                'default' => 10,
                'max' => 10,
            ],
            'relations' => [],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource_4()
    {
        $response = $this->json('OPTIONS', 'jory/band');

        $expected = [
            'fields' => [
                'id' => [
                    'description' => 'The id field.',
                    'default' => true,
                ],
                'name' => [
                    'description' => 'The name field.',
                    'default' => true,
                ],
                'year_start' => [
                    'description' => 'The year in which the band started.',
                    'default' => true,
                ],
                'year_end' => [
                    'description' => 'The year in which the band quitted, could be null if band still exists.',
                    'default' => true,
                ],
                'all_albums_string' => [
                    'description' => 'The all_albums_string field.',
                    'default' => false,
                ],
                'image_urls_string' => [
                    'description' => 'The image_urls_string field.',
                    'default' => false,
                ],
                'titles_string' => [
                    'description' => 'The titles_string field.',
                    'default' => false,
                ],
                'first_title_string' => [
                    'description' => 'The first_title_string field.',
                    'default' => false,
                ],
            ],
            'filters' => [
                'id' => [
                    'description' => 'Try this filter by id!',
                    'operators' => [
                        '=',
                        '>',
                        '<',
                        '<=',
                        '>=',
                        '<>',
                        '!=',
                        'in',
                        'not_in',
                    ],
                ],
                'name' => [
                    'description' => 'Filter on the name field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'year_start' => [
                    'description' => 'Filter on the year_start field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'year_end' => [
                    'description' => 'Filter on the year_end field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'has_album_with_name' => [
                    'description' => 'Filter bands that have an album with a given name.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'number_of_albums_in_year' => [
                    'description' => 'Filter the bands that released a given number of albums in a year, pass value and year parameter.',
                    'operators' => ['=', '>', '<', '<=', '>=', '<>', '!='],
                ],
            ],
            'sorts' => [
                'id' => [
                    'description' => 'Sort by the id field.',
                    'default' => false,
                ],
                'name' => [
                    'description' => 'Sort by the name field.',
                    'default' => false,
                ],
                'year_start' => [
                    'description' => 'Sort by the year_start field.',
                    'default' => false,
                ],
                'year_end' => [
                    'description' => 'Sort by the year_end field.',
                    'default' => false,
                ],
            ],
            'limit' => [
                'default' => 30,
                'max' => 120,
            ],
            'relations' => [
                'albums' => [
                    'description' => 'Get the related albums for the band.',
                    'type' => 'album',
                ],
                'images' => [
                    'description' => 'The images relation.',
                    'type' => 'image',
                ],
                'people' => [
                    'description' => 'The people relation.',
                    'type' => 'person',
                ],
                'songs' => [
                    'description' => 'The songs relation.',
                    'type' => 'song',
                ],
                'first_song' => [
                    'description' => 'The first_song relation.',
                    'type' => 'song',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource_5()
    {
        $response = $this->json('OPTIONS', 'jory/album');

        $expected = [
            'fields' => [
                'id' => [
                    'description' => 'The id field.',
                    'default' => true,
                ],
                'name' => [
                    'description' => 'The name field.',
                    'default' => true,
                ],
                'band_id' => [
                    'description' => 'The band_id field.',
                    'default' => true,
                ],
                'release_date' => [
                    'description' => 'The release_date field.',
                    'default' => true,
                ],
                'custom_field' => [
                    'description' => 'The custom_field field.',
                    'default' => false,
                ],
                'cover_image' => [
                    'description' => 'The cover_image field.',
                    'default' => false,
                ],
                'titles_string' => [
                    'description' => 'The titles_string field.',
                    'default' => false,
                ],
            ],
            'filters' => [
                'id' => [
                    'description' => 'Filter on the id field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'name' => [
                    'description' => 'Filter on the name field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'band_id' => [
                    'description' => 'Filter on the band_id field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'release_date' => [
                    'description' => 'Filter on the release_date field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'number_of_songs' => [
                    'description' => 'Filter on the number_of_songs field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'has_song_with_title' => [
                    'description' => 'Filter on the has_song_with_title field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'album_cover.album_id' => [
                    'description' => 'Filter on the album_cover.album_id field.',
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
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
            ],
            'sorts' => [
                'id' => [
                    'description' => 'Sort by the id field.',
                    'default' => false,
                ],
                'name' => [
                    'description' => 'Sort by the name field.',
                    'default' => false,
                ],
                'band_id' => [
                    'description' => 'Sort by the band_id field.',
                    'default' => false,
                ],
                'release_date' => [
                    'description' => 'Sort by the release_date field.',
                    'default' => false,
                ],
                'number_of_songs' => [
                    'description' => 'Sort by the number_of_songs field.',
                    'default' => false,
                ],
                'band_name' => [
                    'description' => 'Sort by the band_name field.',
                    'default' => false,
                ],
            ],
            'limit' => [
                'default' => null,
                'max' => null,
            ],
            'relations' => [
                'songs' => [
                    'description' => 'The songs relation.',
                    'type' => 'song',
                ],
                'band' => [
                    'description' => 'The band relation.',
                    'type' => 'band',
                ],
                'cover' => [
                    'description' => 'The cover relation.',
                    'type' => 'album-cover',
                ],
                'album_cover' => [
                    'description' => 'The album_cover relation.',
                    'type' => 'album-cover',
                ],
                'snake_case_album_cover' => [
                    'description' => 'The snake_case_album_cover relation.',
                    'type' => 'album-cover',
                ],
                'custom_songs_1' => [
                    'description' => 'The custom_songs_1 relation.',
                    'type' => 'song-with-after-fetch',
                ],
                'custom_songs_2' => [
                    'description' => 'The custom_songs_2 relation.',
                    'type' => 'song',
                ],
                'custom_songs_3' => [
                    'description' => 'The custom_songs_3 relation.',
                    'type' => 'song',
                ],
                'tags' => [
                    'description' => 'The tags relation.',
                    'type' => 'tag',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
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

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_validate_if_a_requested_filter_is_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"f":"title","o":"like","d":"%love%"}}',
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

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_validate_if_a_requested_filter_is_not_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"f":"titel","o":"like","d":"%love%"}}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" is not available for filtering, did you mean "title"? (Location: filter(titel))',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_requested_filters_are_not_available_1()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"and":[{"f":"titel","o":"like","d":"%love%"},{"f":"title","o":"like","d":"%test%"},{"f":"albumm_id","d":11}]}}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" is not available for filtering, did you mean "title"? (Location: filter(and).0(titel))',
                'Field "albumm_id" is not available for filtering, did you mean "album_id"? (Location: filter(and).2(albumm_id))',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_requested_filters_are_not_available_2()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"or":[{"f":"titel","o":"like","d":"%love%"},{"f":"title","o":"like","d":"%test%"},{"f":"albumm_id","d":11}]}}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" is not available for filtering, did you mean "title"? (Location: filter(or).0(titel))',
                'Field "albumm_id" is not available for filtering, did you mean "album_id"? (Location: filter(or).2(albumm_id))',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_the_requested_operator_is_available_on_a_filter()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"and":[{"f":"titel","o":"like","d":"%love%"},{"f":"title","o":"like","d":"%test%"},{"f":"albumm_id","d":11},{"f":"album_id","o":"like","d":11}]}}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" is not available for filtering, did you mean "title"? (Location: filter(and).0(titel))',
                'Field "albumm_id" is not available for filtering, did you mean "album_id"? (Location: filter(and).2(albumm_id))',
                'Operator "like" is not available for field "album_id". (Location: filter(and).3(album_id))',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_a_requested_sort_is_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"f":"title","o":"like","d":"%love%"},"srt":["title"]}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'And the Gods Made Love',
                ],
                [
                    'title' => 'Bold as Love',
                ],
                [
                    'title' => 'Little Miss Lover',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_validate_if_a_requested_sort_is_not_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":3,"flt":{"f":"title","o":"like","d":"%love%"},"srt":["tite","if"]}',
        ]);

        $expected = [
            'errors' => [
                'Field "tite" is not available for sorting, did you mean "title"? (Location: sorts.tite)',
                'Field "if" is not available for sorting, did you mean "id"? (Location: sorts.if)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_the_requested_limit_exceeds_the_maximum()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"flt":{"f":"title","o":"like","d":"%love%"},"lmt":1500}',
        ]);

        $expected = [
            'errors' => [
                'The maximum limit for this resource is 250, please lower your limit or drop the limit parameter. (Location: limit)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_the_requested_limit_does_not_exceed_the_maximum()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"flt":{"f":"title","o":"like","d":"%love"},"srt":["title"],"lmt":250}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'And the Gods Made Love',
                ],
                [
                    'title' => 'Bold as Love',
                ],
                [
                    'title' => 'May This Be Love',
                ],
                [
                    'title' => 'Whole Lotta Love',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
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

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_does_not_validate_on_limit_when_the_max_is_set_to_null()
    {
        $response = $this->json('GET', 'song-two', [
            'jory' => '{"fld":["title"],"flt":{"f":"title","o":"like","d":"%love"},"srt":["title"],"lmt":321311}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'And the Gods Made Love',
                ],
                [
                    'title' => 'Bold as Love',
                ],
                [
                    'title' => 'May This Be Love',
                ],
                [
                    'title' => 'Whole Lotta Love',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_gives_an_error_when_an_offset_is_applied_but_no_limit_is_available_in_request_and_config()
    {
        $response = $this->json('GET', 'song-two', [
            'jory' => '{"ofs":2}',
        ]);

        $expected = [
            'errors' => [
                'An offset cannot be set without a limit. (Location: offset)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_applies_the_max_limit_as_default_when_only_the_max_is_given_and_applies_default_sorts()
    {
        $response = $this->json('GET', 'song-three', [
            'jory' => '{"fld":["title"]}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'You Never Give Me Your Money',
                ],
                [
                    'title' => 'The End',
                ],
                [
                    'title' => 'Sun King',
                ],
                [
                    'title' => 'Something',
                ],
                [
                    'title' => 'She Came in Through the Bathroom Window',
                ],
                [
                    'title' => 'Polythene Pam',
                ],
                [
                    'title' => 'Oh! Darling',
                ],
                [
                    'title' => 'Octopus\'s Garden',
                ],
                [
                    'title' => 'Mean Mr. Mustard',
                ],
                [
                    'title' => 'Maxwell\'s Silver Hammer',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_validate_if_a_requested_relation_is_available_in_a_relation()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":5,"rlt":{"album":{"fld":["name"]}}}',
        ]);

        $expected = [
            'data' => [
                [
                    'title' => 'Gimme Shelter',
                    'album' => [
                        'name' => 'Let it bleed',
                    ],
                ],
                [
                    'title' => 'Love In Vain (Robert Johnson)',
                    'album' => [
                        'name' => 'Let it bleed',
                    ],
                ],
                [
                    'title' => 'Country Honk',
                    'album' => [
                        'name' => 'Let it bleed',
                    ],
                ],
                [
                    'title' => 'Live With Me',
                    'album' => [
                        'name' => 'Let it bleed',
                    ],
                ],
                [
                    'title' => 'Let It Bleed',
                    'album' => [
                        'name' => 'Let it bleed',
                    ],
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_validate_if_a_requested_relation_is_not_available_in_a_relation()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["title"],"lmt":5,"rlt":{"albumm":{"fld":["name"]}}}',
        ]);

        $expected = [
            'errors' => [
                'Relation "albumm" is not available, did you mean "album"? (Location: relations.albumm)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_a_requested_field_is_not_available_in_a_relation()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"fld":["name"],"lmt":5,"rlt":{"albums":{"fld":["namee"]}}}',
        ]);

        $expected = [
            'errors' => [
                'Field "namee" is not available, did you mean "name"? (Location: albums.fields.namee)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_a_requested_filter_is_not_available_in_a_relation()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"fld":["name"],"lmt":5,"rlt":{"albums":{"fld":["namee"],"flt":{"f":"ids"}}}}',
        ]);

        $expected = [
            'errors' => [
                'Field "namee" is not available, did you mean "name"? (Location: albums.fields.namee)',
                'Field "ids" is not available for filtering, did you mean "id"? (Location: albums.filter(ids))',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_a_requested_subfilter_is_not_available_in_a_relation()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"fld":["name"],"lmt":5,"rlt":{"albums":{"fld":["namee"],"flt":{"and":[{"f":"ids"},{"f":"name"},{"f":"relese_date"}]}}},"flt":{"f":"date_start","d":"2018-01-01"}}',
        ]);

        $expected = [
            'errors' => [
                'Field "date_start" is not available for filtering, did you mean "year_start"? (Location: filter(date_start))',
                'Field "namee" is not available, did you mean "name"? (Location: albums.fields.namee)',
                'Field "ids" is not available for filtering, did you mean "id"? (Location: albums.filter(and).0(ids))',
                'Field "relese_date" is not available for filtering, did you mean "release_date"? (Location: albums.filter(and).2(relese_date))',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_a_requested_sort_is_not_available_in_a_relation()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"fld":["name"],"lmt":5,"rlt":{"albums":{"srt":["name","releese_date"],"fld":["namee"],"flt":{"and":[{"f":"ids"},{"f":"name"},{"f":"relese_date"}]}}},"flt":{"f":"date_start","d":"2018-01-01"}}',
        ]);

        $expected = [
            'errors' => [
                'Field "date_start" is not available for filtering, did you mean "year_start"? (Location: filter(date_start))',
                'Field "namee" is not available, did you mean "name"? (Location: albums.fields.namee)',
                'Field "ids" is not available for filtering, did you mean "id"? (Location: albums.filter(and).0(ids))',
                'Field "relese_date" is not available for filtering, did you mean "release_date"? (Location: albums.filter(and).2(relese_date))',
                'Field "releese_date" is not available for sorting, did you mean "release_date"? (Location: albums.sorts.releese_date)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_a_limit_is_exceeded_in_a_relation()
    {
        config()->set('jory.limit.max', 1000);

        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"fld":["name"],"lmt":5,"rlt":{"albums":{"lmt":123134}},"flt":{"f":"date_start","d":"2018-01-01"}}',
        ]);

        $expected = [
            'errors' => [
                'Field "date_start" is not available for filtering, did you mean "year_start"? (Location: filter(date_start))',
                'The maximum limit for this resource is 1000, please lower your limit or drop the limit parameter. (Location: albums.limit)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_validate_if_a_relation_is_available_in_a_relation()
    {
        config()->set('jory.limit.max', 1000);

        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"fld":["name"],"lmt":5,"rlt":{"albums":{"lmt":123134,"rlt":{"band":{},"songgs":{}}}},"flt":{"f":"date_start","d":"2018-01-01"}}',
        ]);

        $expected = [
            'errors' => [
                'Field "date_start" is not available for filtering, did you mean "year_start"? (Location: filter(date_start))',
                'The maximum limit for this resource is 1000, please lower your limit or drop the limit parameter. (Location: albums.limit)',
                'Relation "songgs" is not available, did you mean "songs"? (Location: albums.relations.songgs)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_suggests_only_relevant_alternatives()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => '{"fld":["releaseDate", "releaseDat", "releaseDatee", "reMeasDat", "rrrelease__dddate"],"rlt":{}}',
        ]);

        $expected = [
            'errors' => [
                'Field "releaseDate" is not available, did you mean "release_date"? (Location: fields.releaseDate)',
                'Field "releaseDat" is not available, did you mean "release_date"? (Location: fields.releaseDat)',
                'Field "releaseDatee" is not available, did you mean "release_date"? (Location: fields.releaseDatee)',
                'Field "reMeasDat" is not available, no suggestions found. (Location: fields.reMeasDat)',
                'Field "rrrelease__dddate" is not available, no suggestions found. (Location: fields.rrrelease__dddate)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }
}
