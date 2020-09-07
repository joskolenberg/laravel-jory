<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\User;

class MetadataTest extends TestCase
{

    /** @test */
    public function it_can_return_the_query_count_as_meta_data()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => '{"filter":{"f":"title","o":"=","d":"Wild Horses"},"rlt":{"album":{}}}',
            'meta' => ['query_count'],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 12,
                    'album_id' => 2,
                    'title' => 'Wild Horses',
                    'album' => [
                        'id' => 2,
                        'band_id' => 1,
                        'name' => 'Sticky Fingers',
                        'release_date' => '1971-04-23 00:00:00',
                    ],
                ],
            ],
            'meta' => [
                'query_count' => 2
            ]
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_the_query_count_as_meta_data_2()
    {
        $response = $this->json('GET', 'jory/band/1', [
            'jory' => '{"fld":["name"],"rlt":{"albums:count":{},"songs:count":{}}}',
            'meta' => ['query_count'],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => "Rolling Stones",
                'albums:count' => 3,
                'songs:count' => 37,
            ],
            'meta' => [
                'query_count' => 3
            ]
        ]);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_can_return_the_query_count_as_meta_data_3()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => '{"band:1":{"fld":["name"],"rlt":{"albums:count":{},"songs:count":{}}},"band:2 as led_zeppelin":{"fld":["name"],"rlt":{"albums:count":{},"songs:count":{}}}}',
            'meta' => ['query_count'],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'band:1' => [
                    'name' => "Rolling Stones",
                    'albums:count' => 3,
                    'songs:count' => 37,
                ],
                'led_zeppelin' => [
                    'name' => "Led Zeppelin",
                    'albums:count' => 3,
                    'songs:count' => 28,
                ]],
            'meta' => [
                'query_count' => 6
            ]
        ]);

        $this->assertQueryCount(6);
    }

    /** @test */
    public function it_can_return_the_meta_data_in_camelCase()
    {
        $response = $this->json('GET', 'jory/band/1', [
            'jory' => '{"fld":["name"],"rlt":{"albums:count":{},"songs:count":{}}}',
            'meta' => ['queryCount'],
            'case' => 'camel'
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => "Rolling Stones",
                'albums:count' => 3,
                'songs:count' => 37,
            ],
            'meta' => [
                'queryCount' => 3
            ]
        ]);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_can_return_the_processing_time()
    {
        $response = $this->json('GET', 'jory/song/1', [
            'jory' => '{}',
            'meta' => ['query_count', 'time'],
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'album_id',
                'title',
            ],
            'meta' => [
                'query_count',
                'time'
            ]
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_the_current_users_email()
    {
        $this->actingAs(User::find(3));

        $response = $this->json('GET', 'jory/song', [
            'jory' => '{"filter":{"f":"title","o":"=","d":"Wild Horses"},"rlt":{"album":{}}}',
            'meta' => ['query_count', 'user'],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 12,
                    'album_id' => 2,
                    'title' => 'Wild Horses',
                    'album' => [
                        'id' => 2,
                        'band_id' => 1,
                        'name' => 'Sticky Fingers',
                        'release_date' => '1971-04-23 00:00:00',
                    ],
                ],
            ],
            'meta' => [
                'query_count' => 3,
                'user' => 'ronnie@rollingstones.com',
            ]
        ]);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_returns_null_if_no_user_is_logged_in()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => '{"filter":{"f":"title","o":"=","d":"Wild Horses"},"rlt":{"album":{}}}',
            'meta' => ['query_count', 'user'],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 12,
                    'album_id' => 2,
                    'title' => 'Wild Horses',
                    'album' => [
                        'id' => 2,
                        'band_id' => 1,
                        'name' => 'Sticky Fingers',
                        'release_date' => '1971-04-23 00:00:00',
                    ],
                ],
            ],
            'meta' => [
                'query_count' => 2,
                'user' => null,
            ]
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_give_the_total_records_for_a_single_resource()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => '{"fld":["title"],"filter":{"f":"title","o":"like","d":"%love%"},"lmt":3,"srt":"title"}',
            'meta' => ['total'],
        ]);

        $response->assertStatus(200)->assertExactJson([
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
            'meta' => [
                'total' => 8,
            ]
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_gives_null_as_total_records_for_a_count_request()
    {
        $response = $this->json('GET', 'jory/song/count', [
            'jory' => '{"fld":["title"],"filter":{"f":"title","o":"like","d":"%love%"},"lmt":3}',
            'meta' => ['query_count', 'total'],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => 8,
            'meta' => [
                'query_count' => 1,
                'total' => null,
            ]
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_gives_null_as_total_records_for_an_exists_request()
    {
        $response = $this->json('GET', 'jory/song/exists', [
            'jory' => '{"fld":["title"],"filter":{"f":"title","o":"like","d":"%love%"},"lmt":3}',
            'meta' => ['query_count', 'total'],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => true,
            'meta' => [
                'query_count' => 1,
                'total' => null,
            ]
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_give_the_total_records_for_multiple_resources_and_returns_no_total_for_count_or_show_requests()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'song as lovesongs' => [
                    "fld" => ['title'],
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love%',
                    ],
                    'lmt' => 3,
                    'srt' => ["id"]
                ],
                'song:count as lovesong_count' => [
                    "fld" => ['title'],
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love%',
                    ],
                    'lmt' => 3,
                    'srt' => ["id"]
                ],
                'song as all_songs' => [
                    'fld' => ['title'],
                    'lmt' => 3,
                    'srt' => ["id"]
                ],
                'song:1 as first_song' => [
                    'fld' => ['title'],
                ],
            ],
            'meta' => ['total'],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'lovesongs' => [
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
                'lovesong_count' => 8,
                'all_songs' => [
                    [
                        'title' => 'Gimme Shelter',
                    ],
                    [
                        'title' => 'Love In Vain (Robert Johnson)',
                    ],
                    [
                        'title' => 'Country Honk',
                    ],
                ],
                'first_song' => [
                    'title' => 'Gimme Shelter',
                ],
            ],
            'meta' => [
                'total' => [
                    'lovesongs' => 8,
                    'all_songs' => 147,
                ],
            ]
        ]);

        $this->assertQueryCount(6);
    }

    /** @test */
    public function it_returns_a_422_error_when_unknown_metadata_is_requested()
    {
        $response = $this->json('GET', 'jory/band/1', [
            'jory' => '{"fld":["name"],"rlt":{"albums:count":{},"songs:count":{}}}',
            'meta' => ['total', 'unknown', 'query_count', 'unknown2'],
        ]);

        $response->assertStatus(422)->assertExactJson([
            'errors' => [
                'Meta tag unknown is not supported.',
                'Meta tag unknown2 is not supported.'
            ],
        ]);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_returns_a_422_error_when_metadata_is_requested_while_its_not_supported()
    {
        config()->set('jory.response.data-key', null);

        $response = $this->json('GET', 'jory/band/1', [
            'jory' => '{"fld":["name"],"rlt":{"albums:count":{},"songs:count":{}}}',
            'meta' => ['query_count'],
        ]);

        $response->assertStatus(422)->assertExactJson([
            'errors' => [
                'Meta tags are not supported when data is returned in the root.',
            ],
        ]);

        $this->assertQueryCount(0);
    }
}
