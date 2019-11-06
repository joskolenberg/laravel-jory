<?php

namespace JosKolenberg\LaravelJory\Tests;

class FirstTest extends TestCase
{

    /** @test */
    public function it_can_return_the_first_item_using_the_uri_1()
    {
        $response = $this->json('GET', 'jory/song/first', [
            'jory' => [
                'srt' => ['-title'],
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love%',
                ],
                'fld' => ['title'],
            ]
        ]);

        $expected = [
            'data' => [
                'title' => 'Whole Lotta Love',
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_the_first_item_using_the_uri_2()
    {
        $response = $this->json('GET', 'jory/band/first', [
            'jory' => '{"filter":{"f":"name","o":"like","d":"%zep%"},"rlt":{"albums":{"flt":{"f":"name","o":"like","d":"%III%"}}},"fld":["id","name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
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
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_the_first_item_on_a_relation_1()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","d":"%es%"},"rlt":{"songs:first":{"flt":{"f":"title","o":"like","d":"%love%"},"fld":["title"]}},"fld":["name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Rolling Stones',
                    'songs:first' => [
                        'title' => 'Love In Vain (Robert Johnson)',
                    ],
                ],
                [
                    'name' => 'Beatles',
                    'songs:first' => [
                        'title' => 'Lovely Rita',
                    ],
                ],
            ],
        ]);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_can_return_the_first_item_on_a_relation_2()
    {
        $response = $this->json('GET', 'jory/album/3', [
            'jory' => '{"rlt":{"songs:first as last_song":{"srt":["-id"],"fld":["title"]}},"fld":["name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Exile on main st.',
                'last_song' => [
                    'title' => 'Soul Survivor',
                ],
            ],
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_doesnt_fail_when_requesting_the_first_item_on_a_non_collection_relation()
    {
        $response = $this->json('GET', 'jory/song/first', [
            'jory' => '{"rlt":{"album:first":{"fld":["name"]}},"fld":["title"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'title' => 'Gimme Shelter',
                'album:first' => [
                    'name' => 'Let it bleed',
                ],
            ],
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_the_first_item_when_fetching_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'song:first as first_song' => [
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
                'first_song' => [
                    'title' => 'Whole Lotta Love',
                ],
                'band:first' => [
                    'name' => 'Rolling Stones',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }
}
