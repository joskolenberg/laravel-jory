<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Meta;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class TimeTest extends TestCase
{

    /** @test */
    public function it_can_return_the_query_count_as_meta_data()
    {
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => ['time'],
        ])->assertJsonStructure([
            'data' => [],
            'meta' => [
                'time'
            ]
        ]);
    }
}
