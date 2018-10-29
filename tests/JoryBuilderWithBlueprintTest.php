<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Tests\Controllers\SongWithBlueprintController;

class JoryBuilderWithBlueprintTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Route::get('song', SongWithBlueprintController::class.'@index');
        Route::options('song', SongWithBlueprintController::class.'@options');
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
    }

    /** @test */
    public function it_can_validate_if_a_requested_field_is_not_available()
    {
        $response = $this->json('GET', 'song', [
            'jory' => '{"fld":["titel"],"lmt":5}',
        ]);

        $expected = [
            'errors' => [
                'Field "titel" not available. Did you mean "title"? (Location: fields)',
            ],
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource()
    {
        $response = $this->json('OPTIONS', 'song');

        $expected = [
            'fields' => [
                'id' => [
                    'description' => 'Not defined.',
                    'show_by_default' => true,
                ],
                'title' => [
                    'description' => 'The songs title.',
                    'show_by_default' => true,
                ],
                'album_id' => [
                    'description' => 'Not defined.',
                    'show_by_default' => false,
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
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
    }
}