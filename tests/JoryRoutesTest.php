<?php

namespace JosKolenberg\LaravelJory\Tests;

class JoryRoutesTest extends TestCase
{
    /** @test */
    public function it_can_return_multiple_records()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","d":"%zep%"}}',
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

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_multiple_records_with_jory_data_applied()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","d":"%zep%"},"rlt":{"albums":{"flt":{"f":"name","o":"like","d":"%III%"},"fld":["id","name","release_date"]}},"fld":["id","name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'albums' => [
                        [
                            'id' => 6,
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
    public function it_can_return_a_single_record()
    {
        $response = $this->json('GET', 'jory/band/3', [
            'jory' => '{"fld":["id","name"],"rlt":{"albums":{"rlt":{"songs":{"flt":{"f":"title","o":"like","d":"%love%"}}},"srt":["-release_date"]}}}',
        ]);

        $expected = [
            'data' => [
                'id' => 3,
                'name' => 'Beatles',
                'albums' => [
                    [
                        'id' => 9,
                        'band_id' => 3,
                        'name' => 'Let it be',
                        'release_date' => '1970-05-08 00:00:00',
                        'songs' => [],
                    ],
                    [
                        'id' => 8,
                        'band_id' => 3,
                        'name' => 'Abbey road',
                        'release_date' => '1969-09-26 00:00:00',
                        'songs' => [],
                    ],
                    [
                        'id' => 7,
                        'band_id' => 3,
                        'name' => 'Sgt. Peppers lonely hearts club band',
                        'release_date' => '1967-06-01 00:00:00',
                        'songs' => [
                            [
                                'id' => 75,
                                'album_id' => 7,
                                'title' => 'Lovely Rita',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_can_return_the_record_count()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '{}',
        ]);

        $expected = [
            'data' => 147,
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_the_record_count_and_should_ignore_pagination()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '{"ofs":3,"lmt":10}',
        ]);

        $expected = [
            'data' => 147,
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_the_record_count_with_a_filter_applied()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '{"flt":{"f":"title","o":"like","d":"%love%"},"ofs":3,"lmt":10}',
        ]);

        $expected = [
            'data' => 8,
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_the_record_count_with_a_custom_filter_applied()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '{"flt":{"and":[{"f":"title","o":"like","d":"%love%"},{"f":"album_name","o":"like","d":"%experienced%"}]},"ofs":3,"lmt":10}',
        ]);

        $expected = [
            'data' => 2,
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_collection()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => '}',
        ]);

        $expected = [
            'errors' => [
                'Jory string is no valid json.',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_single_record()
    {
        $response = $this->json('GET', 'jory/song/2', [
            'jory' => '}',
        ]);

        $expected = [
            'errors' => [
                'Jory string is no valid json.',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_count()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '}',
        ]);

        $expected = [
            'errors' => [
                'Jory string is no valid json.',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_collection_2()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => '{"filter":{"f":"title","o":"=","d":"The End"},"rlt":{"album":{"rlt":{"songs":{"flt":{"wrong":"parameter"}}}}}}',
        ]);

        $expected = [
            'errors' => [
                'A filter should contain one of the these fields: "f", "field", "and", "group_and", "or" or "group_or". (Location: album.songs.filter)',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_load_multiple_resources_at_once()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => '{"band as btls":{"flt":{"f":"id","d":3}},"band:2 as ledz":{"rlt":{"albums":{"flt":{"f":"name","o":"like","d":"%II%"}}}},"song:count as number_of_songs":{"flt":{"f":"title","o":"like","d":"%Love%"}},"song as lovesongs":{"flt":{"f":"title","o":"like","d":"%Love%"},"fld":["title"],"srt":["title"]},"song":{"flt":{"f":"title","o":"like","d":"%Lovel%"},"fld":["title"],"srt":["title"]},"band:count":{"flt":{"f":"name","o":"like","d":"%r%"}},"person:3":{"fld":["first_name","last_name"]}}',
        ]);

        $expected = [
            'data' => [
                'btls' => [
                    [
                        'id' => 3,
                        'name' => 'Beatles',
                        'year_start' => 1960,
                        'year_end' => 1970,
                    ],
                ],
                'ledz' => [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                    'albums' => [
                        [
                            'id' => 5,
                            'band_id' => 2,
                            'name' => 'Led Zeppelin II',
                            'release_date' => '1969-10-22 00:00:00',
                        ],
                        [
                            'id' => 6,
                            'band_id' => 2,
                            'name' => 'Led Zeppelin III',
                            'release_date' => '1970-10-05 00:00:00',
                        ],
                    ],
                ],
                'number_of_songs' => 8,
                'lovesongs' => [
                    [
                        'title' => 'And the Gods Made Love',
                    ],
                    [
                        'title' => 'Bold as Love',
                    ],
                    [
                        'title' => 'Little Miss Lover',
                    ],
                    [
                        'title' => 'Love In Vain (Robert Johnson)',
                    ],
                    [
                        'title' => 'Love or Confusion',
                    ],
                    [
                        'title' => 'Lovely Rita',
                    ],
                    [
                        'title' => 'May This Be Love',
                    ],
                    [
                        'title' => 'Whole Lotta Love',
                    ],
                ],
                'song' => [
                    [
                        'title' => 'Lovely Rita',
                    ],
                ],
                'band:count' => 2,
                'person:3' => [
                    'first_name' => 'Ronnie',
                    'last_name' => 'Wood',
                ],
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(8);
    }

    /** @test */
    public function it_returns_an_error_when_a_resource_is_not_found()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => '{"bandd":{"flt":{"f":"id","d":3}},"lbmCovrrr":{"flt":{"f":"id","d":3}},"band:2 as ledz":{"rlt":{"albums":{"flt":{"f":"name","o":"like","d":"%II%"}}}},"song as lovesongs":{"flt":{"f":"title","o":"like","d":"%Love%"},"fld":["title"],"srt":["title"]},"son as lovesong":{"flt":{"f":"title","o":"like","d":"%Love%"},"fld":["title"],"srt":["title"]}}',
        ]);

        $expected = [
            'errors' => [
                'Resource bandd not found, did you mean "band"?',
                'Resource lbmCovrrr not found, no suggestions found.',
                'Resource son not found, did you mean "song"?',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_an_error_when_a_JoryException_has_occured()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => '{"song":{"flt":{"f":"title","o":"like","d":"%Love%"},"fld":[title"],"srt":["title"]}}',
        ]);

        $expected = [
            'errors' => [
                'Jory string is no valid json.',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_an_error_when_a_LaravelJoryCallException_has_occured()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => '{"band":{"flt":{"f":"name","o":"like","d":"%bea%"},"fld":["name"],"srt":["naame"]},"band as band_2":{"flt":{"f":"name","o":"like","d":"%bea%"},"fld":["name"],"srt":["naame"],"rlt":{"songgs":{}}}}',
        ]);

        $expected = [
            'errors' => [
                'band: Field "naame" is not available for sorting, did you mean "name"? (Location: sorts.naame)',
                'band as band_2: Field "naame" is not available for sorting, did you mean "name"? (Location: sorts.naame)',
                'band as band_2: Relation "songgs" is not available, did you mean "songs"? (Location: relations.songgs)',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_a_404_when_an_unknown_model_is_configured()
    {
        $this->json('GET', 'jory/bandd', [
            'jory' => '{"flt":{"f":"name","o":"like","d":"%bea%"},"fld":["name"],"srt":["naame"]}',
        ])->assertStatus(404);

        $this->json('GET', 'jory/bandd/3')->assertStatus(404);

        $this->json('GET', 'jory/bandd/count')->assertStatus(404);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_null_when_a_model_is_not_found_by_id_when_loading_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => '{"person:3":{"fld":["first_name","last_name"]},"song:1234":{}}',
        ]);

        $expected = [
            'data' => [
                'person:3' => [
                    'first_name' => 'Ronnie',
                    'last_name' => 'Wood',
                ],
                'song:1234' => null,
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_handle_an_incoming_array_instead_of_json()
    {
        $response = $this->json('GET', 'jory/song/75', [
            'jory' => [
                'fld' => ['title'],
            ],
        ]);

        $expected = [
            'data' => [
                'title' => 'Lovely Rita',
            ],
        ];

        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_handle_an_incoming_array_instead_of_json_with_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'person:3' => [
                    'fld' => ['first_name', 'last_name'],
                ],
                'song:1234' => [],
            ],
        ]);

        $expected = [
            'data' => [
                'person:3' => [
                    'first_name' => 'Ronnie',
                    'last_name' => 'Wood',
                ],
                'song:1234' => null,
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_returns_404_when_an_get_call_for_an_unknown_resource_is_done()
    {
        $response = $this->json('GET', 'jory/persn', [
            'jory' => '{}',
        ]);

        $expected = [
            'errors' => [
                'Resource persn not found, did you mean "person"?',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(404)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_404_when_a_single_call_for_an_unknown_resource_is_done()
    {
        $response = $this->json('GET', 'jory/persn/4', [
            'jory' => '{}',
        ]);

        $expected = [
            'errors' => [
                'Resource persn not found, did you mean "person"?',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(404)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_404_when_a_count_call_for_an_unknown_resource_is_done()
    {
        $response = $this->json('GET', 'jory/persn/count', [
            'jory' => '{}',
        ]);

        $expected = [
            'errors' => [
                'Resource persn not found, did you mean "person"?',
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(404)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(0);
    }
}
