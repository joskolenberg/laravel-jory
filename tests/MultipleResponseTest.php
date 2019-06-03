<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Http\Controllers\JoryController;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class MultipleResponseTest extends TestCase
{

    /** @test */
    public function it_can_return_multiple_resources_applying_json()
    {
        $actual = Jory::multiple()
            ->applyJson('{"song":{"filter":{"f":"title","o":"like","d":"%love"},"fld":["title"]},"song:count as songcount":{"filter":{"f":"title","o":"like","d":"%love"},"fld":["title"]}}')
            ->toArray();

        $this->assertEquals([
            'song' => [
                ['title' => 'Whole Lotta Love'],
                ['title' => 'May This Be Love'],
                ['title' => 'Bold as Love'],
                ['title' => 'And the Gods Made Love'],
            ],
            'songcount' => 4,
        ], $actual);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_multiple_resources_applying_json_using_apply()
    {
        $actual = Jory::multiple()
            ->apply('{"song":{"filter":{"f":"title","o":"like","d":"%love"},"fld":["title"]},"song:count as songcount":{"filter":{"f":"title","o":"like","d":"%love"},"fld":["title"]}}')
            ->toArray();

        $this->assertEquals([
            'song' => [
                ['title' => 'Whole Lotta Love'],
                ['title' => 'May This Be Love'],
                ['title' => 'Bold as Love'],
                ['title' => 'And the Gods Made Love'],
            ],
            'songcount' => 4,
        ], $actual);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_multiple_resources_applying_an_array()
    {
        $actual = Jory::multiple()
            ->applyArray([
                'song' => [
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                    'fld' => ['title']
                ],
                'song:count as songcount' => [
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                    'fld' => ['title']
                ]
            ])
            ->toArray();

        $this->assertEquals([
            'song' => [
                ['title' => 'Whole Lotta Love'],
                ['title' => 'May This Be Love'],
                ['title' => 'Bold as Love'],
                ['title' => 'And the Gods Made Love'],
            ],
            'songcount' => 4,
        ], $actual);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_return_multiple_resources_applying_an_array_using_apply()
    {
        $actual = Jory::multiple()
            ->apply([
                'song' => [
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                    'fld' => ['title']
                ],
                'song:count as songcount' => [
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                    'fld' => ['title']
                ]
            ])
            ->toArray();

        $this->assertEquals([
            'song' => [
                ['title' => 'Whole Lotta Love'],
                ['title' => 'May This Be Love'],
                ['title' => 'Bold as Love'],
                ['title' => 'And the Gods Made Love'],
            ],
            'songcount' => 4,
        ], $actual);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_defaults_to_applying_the_data_in_the_request_when_nothing_is_applied()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'song' => [
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                    'fld' => ['title']
                ],
                'song:count as songcount' => [
                    'flt' => [
                        'f' => 'title',
                        'o' => 'like',
                        'd' => '%love',
                    ],
                    'fld' => ['title']
                ],
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'song' => [
                    ['title' => 'Whole Lotta Love'],
                    ['title' => 'May This Be Love'],
                    ['title' => 'Bold as Love'],
                    ['title' => 'And the Gods Made Love'],
                ],
                'songcount' => 4,
            ]
        ]);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_throws_an_exception_when_invalid_data_is_applied()
    {
        $this->expectException(LaravelJoryException::class);
        $this->expectExceptionMessage('Unexpected type given. Please provide an array or Json string.');

        Jory::multiple()->apply(new \stdClass());
    }
}

