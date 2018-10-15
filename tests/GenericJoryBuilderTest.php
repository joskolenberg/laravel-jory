<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Support\Facades\Route;
use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\LaravelJory\GenericJoryBuilder;
use JosKolenberg\LaravelJory\Tests\Controllers\BandController;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class GenericJoryBuilderTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Route::get('/band', BandController::class.'@index');
        Route::get('/band-as-response', BandController::class.'@indexAsResponse');
    }

    /**
     * @test
     */
    public function it_can_apply_on_a_querybuilder_instance()
    {
        $query = Band::query();
        $actual = (new GenericJoryBuilder())->onQuery($query)->get()->pluck('name')->toArray();

        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_json_string()
    {
        $actual = Song::jory()
            ->applyJson('{"filter":{"f":"title","o":"like","v":"%love"}}')
            ->get()
            ->pluck('title')
            ->toArray();

        $this->assertEquals([
            'Whole Lotta Love',
            'May This Be Love',
            'Bold as Love',
            'And the Gods Made Love',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_array()
    {
        $actual = Song::jory()
            ->applyArray([
                'filter' => [
                    'f' => 'title',
                    'o' => 'like',
                    'v' => 'love%',
                ],
            ])
            ->get()
            ->pluck('title')
            ->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Lovely Rita',
            'Love or Confusion',
        ], $actual);
    }

    /** @test */
    public function it_can_apply_a_jory_json_string_from_a_request()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"filter":{"f":"name","o":"like","v":"%zep%"}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id'   => 2,
                    'name' => 'Led Zeppelin',
                ],
            ]);
    }

    /** @test */
    public function it_can_apply_a_jory_object()
    {
        $jory = (new ArrayParser([
            'filter' => [
                'f' => 'title',
                'o' => 'like',
                'v' => 'love%',
            ],
        ]))->getJory();

        $actual = Song::jory()
            ->applyJory($jory)
            ->get()
            ->pluck('title')
            ->toArray();

        $this->assertEquals([
            'Love In Vain (Robert Johnson)',
            'Lovely Rita',
            'Love or Confusion',
        ], $actual);
    }

    /** @test */
    public function it_defaults_to_empty_when_no_jory_is_applied()
    {
        $actual = Band::jory()
            ->get()
            ->pluck('name')
            ->toArray();

        $this->assertEquals([
            'Rolling Stones',
            'Led Zeppelin',
            'Beatles',
            'Jimi Hendrix Experience',
        ], $actual);
    }

    /** @test */
    public function it_can_be_returned_as_a_response_from_a_controller()
    {
        $response = $this->json('GET', '/band-as-response', [
            'jory' => '{"filter":{"f":"name","o":"like","v":"%zep%"}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id'   => 2,
                    'name' => 'Led Zeppelin',
                ],
            ]);
    }
}
