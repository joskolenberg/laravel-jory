<?php

namespace JosKolenberg\LaravelJory\Tests;

class ExistsTest extends TestCase
{

    /** @test */
    public function it_can_tell_if_an_item_exists_using_the_uri_1()
    {
        $response = $this->json('GET', 'jory/song/exists', [
            'jory' => [
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love%',
                ],
            ]
        ]);

        $expected = [
            'data' => true,
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_tell_if_an_item_exists_using_the_uri_2()
    {
        $response = $this->json('GET', 'jory/band/exists', [
            'jory' => '{"filter":{"f":"name","o":"like","d":"%zep%"},"rlt":{"albums":{"flt":{"f":"name","o":"like","d":"%III%"}}},"fld":["id","name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => true
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_tell_if_an_item_exists_using_the_uri_3()
    {
        $response = $this->json('GET', 'jory/song/exists', [
            'jory' => [
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%lovvve%',
                ],
            ]
        ]);

        $expected = [
            'data' => false,
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_tell_if_a_relation_exists_1()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","d":"%es%"},"rlt":{"songs:exists":{"flt":{"f":"title","o":"like","d":"%gimme%"},"fld":["title"]}},"fld":["name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Rolling Stones',
                    'songs:exists' => true,
                ],
                [
                    'name' => 'Beatles',
                    'songs:exists' => false,
                ],
            ],
        ]);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_can_tell_if_a_relation_exists_2()
    {
        $response = $this->json('GET', 'jory/album/3', [
            'jory' => '{"rlt":{"songs:exists as song_exists":{"srt":["-id"],"fld":["title"]}},"fld":["name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Exile on main st.',
                'song_exists' => true,
            ],
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_doesnt_fail_when_requesting_exists_on_a_non_collection_relation()
    {
        $response = $this->json('GET', 'jory/song/first', [
            'jory' => '{"rlt":{"album:exists":{"fld":["name"]}},"fld":["title"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'title' => 'Gimme Shelter',
                'album:exists' => true,
            ],
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_apply_exists_when_fetching_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'song:exists as song_exists' => [
                    'srt' => ['-title'],
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love%',
                    ],
                    'fld' => ['title'],
                ],
                'band:first' => [
                    'srt' => ['id'],
                    'fld' => ['name'],
                ]
            ]
        ]);

        $expected = [
            'data' => [
                'song_exists' => true,
                'band:first' => [
                    'name' => 'Rolling Stones',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }
}
