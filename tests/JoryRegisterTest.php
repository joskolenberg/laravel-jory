<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class JoryRegisterTest extends TestCase
{

    /** @test */
    public function it_does_throw_an_exception_when_no_associated_jory_resource_is_found_when_the_relation_is_requested()
    {
        $this->expectException(RegistrationNotFoundException::class);
        $this->expectExceptionMessage('No joryResource found for model JosKolenberg\LaravelJory\Tests\Models\ModelWithoutJoryResource. Does JosKolenberg\LaravelJory\Tests\Models\ModelWithoutJoryResource have an associated JoryResource?');

        Jory::on(Song::find(1))->apply([
            'rlt' => [
                'testRelationWithoutJoryResource' => []
            ]
        ])->toArray();
    }
    /** @test */
    public function it_doesnt_throw_an_exception_when_no_associated_jory_resource_is_found_as_long_as_the_relation_isnt_requested()
    {
        $response = $this->json('GET', 'jory/song/1', [
            'jory' => [
                'fld' => ['title'],
            ]
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_does_throw_an_exception_when_no_associated_jory_resource_is_found_when_the_relation_is_requested_1()
    {
        $response = $this->json('GET', 'jory/song/1', [
            'jory' => [
                'fld' => ['title'],
                'rlt' => [
                    'testRelationWithoutJoryResource' => []
                ]
            ]
        ]);
        $response->assertStatus(500);
    }

}
