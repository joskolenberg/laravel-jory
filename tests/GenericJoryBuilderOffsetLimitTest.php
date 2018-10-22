<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class GenericJoryBuilderOffsetLimitTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();

        Song::joryRoutes('song');
        Band::joryRoutes('band');
    }

    /** @test */
    public function it_cannot_apply_an_offset_without_a_limit()
    {
        $this->expectException(LaravelJoryException::class);
        $this->expectExceptionMessage('An offset cannot be set without a limit.');

        Song::jory()->applyJson('{"offset":140}')->getModels();
    }

    /** @test */
    public function it_can_apply_an_offset_and_limit()
    {
        $response = $this->json('GET', '/song', [
            'jory' => '{"offset":140,"limit":20}',
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'id' => 141,
                    'album_id' => 12,
                    'title' => 'Rainy Day, Dream Away',
                ],
                [
                    'id' => 142,
                    'album_id' => 12,
                    'title' => '1983... (A Merman I Should Turn to Be)',
                ],
                [
                    'id' => 143,
                    'album_id' => 12,
                    'title' => 'Moon, Turn the Tides...Gently Gently Away',
                ],
                [
                    'id' => 144,
                    'album_id' => 12,
                    'title' => 'Still Raining, Still Dreaming',
                ],
                [
                    'id' => 145,
                    'album_id' => 12,
                    'title' => 'House Burning Down',
                ],
                [
                    'id' => 146,
                    'album_id' => 12,
                    'title' => 'All Along the Watchtower',
                ],
                [
                    'id' => 147,
                    'album_id' => 12,
                    'title' => 'Voodoo Child (Slight Return)',
                ],
            ]);
    }

    /** @test */
    public function it_can_apply_a_limit_without_an_offset()
    {
        $response = $this->json('GET', '/song', [
            'jory' => '{"limit":3}',
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'id' => 1,
                    'album_id' => 1,
                    'title' => 'Gimme Shelter',
                ],
                [
                    'id' => 2,
                    'album_id' => 1,
                    'title' => 'Love In Vain (Robert Johnson)',
                ],
                [
                    'id' => 3,
                    'album_id' => 1,
                    'title' => 'Country Honk',
                ],
            ]);
    }

    /** @test */
    public function it_can_apply_an_offset_and_limit_combined_with_with_sorts_and_filters()
    {
        $response = $this->json('GET', '/song', [
            'jory' => '{"flt":{"f":"title","o":"like","v":"%love%"},"srt":{"title":"asc"},"offset":2,"limit":3}',
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'id' => 130,
                    'album_id' => 11,
                    'title' => 'Little Miss Lover',
                ],
                [
                    'id' => 2,
                    'album_id' => 1,
                    'title' => 'Love In Vain (Robert Johnson)',
                ],
                [
                    'id' => 112,
                    'album_id' => 10,
                    'title' => 'Love or Confusion',
                ],
            ]);
    }

    /** @test */
    public function it_can_apply_an_offset_and_limit_combined_with_with_sorts_and_filters_on_relations()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"flt":{"f":"name","v":"Beatles"},"rlt":{"songs":{"flt":{"f":"title","o":"like","v":"%a%"},"srt":{"title":"asc"},"offset":10,"limit":5,"fld":["id","title"]}}}'
        ]);

        $response
            ->assertStatus(200)
            ->assertExactJson([
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
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
            ]);
    }
}
