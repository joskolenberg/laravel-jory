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
            [
                'id' => 2,
                'name' => 'Led Zeppelin',
                'year_start' => 1968,
                'year_end' => 1980,
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
        ]);
    }

    /** @test */
    public function it_can_return_a_single_record()
    {
        $response = $this->json('GET', 'jory/band/3', [
            'jory' => '{"fld":["id","name"],"rlt":{"albums":{"rlt":{"songs":{"flt":{"f":"title","o":"like","v":"%love%"}}},"srt":{"release_date":"desc"}}}}',
        ]);

        $expected = [
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
        ];

        // ExactJson doesn't tell if the sort order is right so do both checks.
        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);
    }
}