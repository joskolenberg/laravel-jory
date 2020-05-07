<?php

namespace JosKolenberg\LaravelJory\Tests;

class OffsetLimitTest extends TestCase
{
    /** @test */
    public function it_can_apply_an_offset_and_limit()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => [
                'fld' => 'title',
                'offset' => 140,
                'limit' => 20,
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'title' => 'Rainy Day, Dream Away',
                ],
                [
                    'title' => '1983... (A Merman I Should Turn to Be)',
                ],
                [
                    'title' => 'Moon, Turn the Tides...Gently Gently Away',
                ],
                [
                    'title' => 'Still Raining, Still Dreaming',
                ],
                [
                    'title' => 'House Burning Down',
                ],
                [
                    'title' => 'All Along the Watchtower',
                ],
                [
                    'title' => 'Voodoo Child (Slight Return)',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_limit_without_an_offset()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => [
                'fld' => 'title',
                'lmt' => 3,
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
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
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_an_offset_and_limit_combined_with_with_sorts_and_filters()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => [
                'fld' => 'title',
                'flt' =>
                    [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love%',
                    ],
                'srt' => 'title',
                'offset' => 2,
                'limit' => 3,
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'title' => 'Little Miss Lover',
                ],
                [
                    'title' => 'Love In Vain (Robert Johnson)',
                ],
                [
                    'title' => 'Love or Confusion',
                ],
            ],
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_an_offset_and_limit_combined_with_with_sorts_and_filters_on_relations()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => 'name',
                'flt' =>
                    [
                        'f' => 'name',
                        'd' => 'Beatles',
                    ],
                'rlt' =>
                    [
                        'songs' =>
                            [
                                'flt' =>
                                    [
                                        'f' => 'title',
                                        'o' => 'like',
                                        'd' => '%a%',
                                    ],
                                'srt' => 'title',
                                'offset' => 10,
                                'limit' => 5,
                                'fld' =>
                                    [
                                        'id',
                                        'title',
                                    ],
                            ],
                    ],
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Beatles',
                    'songs' => [
                        [
                            'id' => 103,
                            'title' => 'I\'ve Got a Feeling',
                        ],
                        [
                            'id' => 75,
                            'title' => 'Lovely Rita',
                        ],
                        [
                            'id' => 68,
                            'title' => 'Lucy in the Sky with Diamonds',
                        ],
                        [
                            'id' => 102,
                            'title' => 'Maggie Mae',
                        ],
                        [
                            'id' => 81,
                            'title' => 'Maxwell\'s Silver Hammer',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertQueryCount(2);
    }
}
