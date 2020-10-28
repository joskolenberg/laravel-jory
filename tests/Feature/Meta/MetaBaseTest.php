<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Meta;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class MetaBaseTest extends TestCase
{
    /** @test */
    public function it_can_return_multiple_meta_data()
    {
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => ['query_count', 'time'],
        ])->assertJsonStructure([
            'data' => [],
            'meta' => [
                'query_count',
                'time'
            ]
        ]);
    }

    /** @test */
    public function it_can_process_an_empty_array_as_meta_data()
    {
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => [],
        ])->assertJsonStructure([
            'data' => [],
        ]);
    }

    /** @test */
    public function it_returns_a_422_error_when_unknown_metadata_is_requested()
    {
        Jory::register(UserJoryResource::class);
        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => ['unknown'],
        ])->assertStatus(422)->assertExactJson([
            'errors' => [
                'Meta tag unknown is not supported.',
            ],
        ]);
    }

    /** @test */
    public function it_returns_a_422_error_when_multiple_unknown_metadata_is_requested()
    {
        Jory::register(UserJoryResource::class);
        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => ['unknown', 'query_count', 'unknown2'],
        ])->assertStatus(422)->assertExactJson([
            'errors' => [
                'Meta tag unknown is not supported.',
                'Meta tag unknown2 is not supported.',
            ],
        ]);
    }

    /** @test */
    public function it_returns_a_422_error_when_metadata_is_requested_while_its_not_supported()
    {
        config()->set('jory.response.data_key', null);

        Jory::register(UserJoryResource::class);
        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => ['unknown', 'query_count', 'unknown2'],
        ])->assertStatus(422)->assertExactJson([
            'errors' => [
                'Meta tags are not supported when data is returned in the root.',
            ],
        ]);
    }}
