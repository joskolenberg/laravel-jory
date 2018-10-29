<?php

namespace JosKolenberg\LaravelJory\Tests;

class JoryRoutesTest extends TestCase
{
    /** @test */
    public function it_can_return_multiple_records()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","v":"%zep%"}}',
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
    }

    /** @test */
    public function it_can_return_multiple_records_with_jory_data_applied()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","v":"%zep%"},"rlt":{"albums":{"flt":{"f":"name","o":"like","v":"%III%"},"fld":["id","name","release_date"]}},"fld":["id","name"]}',
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
                            'release_date' => '1970-10-05',
                        ],

                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_return_a_single_record()
    {
        $response = $this->json('GET', 'jory/band/3', [
            'jory' => '{"fld":["id","name"],"rlt":{"albums":{"rlt":{"songs":{"flt":{"f":"title","o":"like","v":"%love%"}}},"srt":["-release_date"]}}}',
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
                        'release_date' => '1970-05-08',
                        'songs' => [],
                    ],
                    [
                        'id' => 8,
                        'band_id' => 3,
                        'name' => 'Abbey road',
                        'release_date' => '1969-09-26',
                        'songs' => [],
                    ],
                    [
                        'id' => 7,
                        'band_id' => 3,
                        'name' => 'Sgt. Peppers lonely hearts club band',
                        'release_date' => '1967-06-01',
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
    }

    /** @test */
    public function it_can_return_the_record_count_with_a_filter_applied()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '{"flt":{"f":"title","o":"like","v":"%love%"},"ofs":3,"lmt":10}',
        ]);

        $expected = [
            'data' => 8,
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);
    }

    /** @test */
    public function it_can_return_the_record_count_with_a_custom_filter_applied()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '{"flt":{"and":[{"f":"title","o":"like","v":"%love%"},{"f":"album_name","o":"like","v":"%experienced%"}]},"ofs":3,"lmt":10}',
        ]);

        $expected = [
            'data' => 2,
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_collection()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => '}',
        ]);

        $expected = [
            'errors' => [
                'Jory string is no valid json.'
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_single_record()
    {
        $response = $this->json('GET', 'jory/song/2', [
            'jory' => '}',
        ]);

        $expected = [
            'errors' => [
                'Jory string is no valid json.'
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_count()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '}',
        ]);

        $expected = [
            'errors' => [
                'Jory string is no valid json.'
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_collection_2()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => '{"filter":{"f":"title","o":"=","v":"The End"},"rlt":{"album":{"rlt":{"songs":{"flt":{"wrong":"parameter"}}}}}}',
        ]);

        $expected = [
            'errors' => [
                'A filter should contain one of the these fields: "f", "field", "and", "group_and", "or" or "group_or". (Location: album.songs.filter)'
            ],
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(422)->assertJson($expected)->assertExactJson($expected);
    }

    /** @test */
    public function it_can_return_the_options_for_a_resource()
    {
        $response = $this->json('OPTIONS', 'jory/song');

        $expected = [
            'fields' => 'Not defined.',
            'filters' => 'Not defined.',
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);
    }
}
