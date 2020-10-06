<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Http\Controllers\JoryController;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Responses\JoryResponse;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAlternateUri;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class ResponseTest extends TestCase
{

    /** @test */
    public function it_can_apply_on_a_model_class()
    {
        $actual = Jory::onModelClass(Song::class)
            ->apply([
                'flt' =>
                    [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                'fld' => 'title',
            ])
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'May This Be Love'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_on_a_query()
    {
        $actual = Jory::onQuery(Song::query()->where('title', 'like', '%ol%'))
            ->apply([
                'flt' =>
                    [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                'fld' => 'title',
            ])
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'Bold as Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_a_single_record_when_applying_a_model_instance()
    {
        $actual = Jory::onModel(Song::find(47))
            ->apply([
                'fld' => 'title',
                'rlt' =>
                    [
                        'album' =>
                            [
                                'fld' => 'name',
                            ],
                    ],
            ])
            ->toArray();

        $this->assertEquals([
            'title' => 'Whole Lotta Love',
            'album' => [
                'name' => 'Led Zeppelin II',
            ]
        ], $actual);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_can_apply_on_a_resource_by_uri()
    {
        Jory::register(SongJoryResourceWithAlternateUri::class);

        $actual = Jory::byUri('ssoonngg')
            ->apply([
                'flt' =>
                    [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                'fld' => 'title',
            ])
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'May This Be Love'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_json_string()
    {
        $actual = Jory::on(Song::class)
            ->applyJson(json_encode([
                'flt' =>
                    [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                'fld' => 'title',
            ]))
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'May This Be Love'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_a_json_string_using_apply()
    {
        $actual = Jory::on(Song::class)
            ->apply(json_encode([
                'flt' =>
                    [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                'fld' => 'title',
            ]))
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'May This Be Love'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_an_array()
    {
        $actual = Jory::on(Song::class)
            ->applyArray([
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love',
                ],
                'fld' => ['title']
            ])
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'May This Be Love'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_an_array_using_apply()
    {
        $actual = Jory::on(Song::class)
            ->apply([
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love',
                ],
                'fld' => ['title']
            ])
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'May This Be Love'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_throws_an_exception_when_an_invalid_type_is_applied()
    {
        $this->expectException(LaravelJoryException::class);
        $this->expectExceptionMessage('Unexpected type given. Please provide an array or Json string.');

        Jory::on(Song::class)
            ->apply(new JoryController());
    }

    /** @test */
    public function it_defaults_to_applying_the_data_in_the_request_when_nothing_is_applied()
    {
        $response = $this->json('GET', 'jory/song', [
            'jory' => [
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love',
                ],
                'fld' => ['title']
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                ['title' => 'Whole Lotta Love'],
                ['title' => 'May This Be Love'],
                ['title' => 'Bold as Love'],
                ['title' => 'And the Gods Made Love'],
            ]
        ]);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_the_record_count()
    {
        $actual = Jory::on(Song::class)
            ->apply([
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love',
                ],
                'rlt' => [
                    'album' => [] // Add a relation to check if they are ignored
                ]
            ])
            ->count()
            ->toArray();

        $this->assertEquals(4, $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_a_single_record()
    {
        $actual = Jory::on(Song::class)
            ->apply([
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love',
                ],
                'fld' => [
                    'title',
                ],
                'rlt' => [
                    'album' => [
                        'fld' => ['name']
                    ]
                ]
            ])
            ->first()
            ->toArray();

        $this->assertEquals([
            'title' => 'Whole Lotta Love',
            'album' => [
                'name' => 'Led Zeppelin II',
            ]
        ], $actual);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_a_single_record_based_on_the_id()
    {
        $actual = Jory::on(Song::class)
            ->find(47)
            ->apply([
                'fld' => [
                    'title',
                ],
                'rlt' => [
                    'album' => [
                        'fld' => ['name']
                    ]
                ]
            ])
            ->toArray();

        $this->assertEquals([
            'title' => 'Whole Lotta Love',
            'album' => [
                'name' => 'Led Zeppelin II',
            ]
        ], $actual);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_throws_an_exception_when_no_resource_has_been_set_on_the_response()
    {
        $this->expectException(LaravelJoryException::class);
        $this->expectExceptionMessage('No resource has been set on the JoryResponse. Use the on() method to set a resource.');

        $response = new JoryResponse(app()->make('request'), app()->make(JoryResourcesRegister::class));
        $response->toArray();
    }
}

