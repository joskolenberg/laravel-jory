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
}
