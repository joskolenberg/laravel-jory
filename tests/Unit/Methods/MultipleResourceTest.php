<?php

namespace JosKolenberg\LaravelJory\Tests\Unit\Methods;

use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class MultipleResourceTest extends TestCase
{

    /** @test */
    public function it_can_return_multiple_resources_applying_json()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $actual = Jory::multiple()
            ->applyJson(json_encode([
                'user:first' => [
                        'fld' => 'name',
                    ],
                'team:first' => [
                        'fld' => 'name',
                    ],
            ]))
            ->toArray();

        $this->assertEquals([
            'user:first' => [
                'name' => 'Bert',
            ],
            'team:first' => [
                'name' => 'Sesame Street',
            ],
        ], $actual);
    }

    /** @test */
    public function it_can_return_multiple_resources_applying_json_using_apply()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $actual = Jory::multiple()
            ->apply(json_encode([
                'user:first' => [
                    'fld' => 'name',
                ],
                'team:first' => [
                    'fld' => 'name',
                ],
            ]))
            ->toArray();

        $this->assertEquals([
            'user:first' => [
                'name' => 'Bert',
            ],
            'team:first' => [
                'name' => 'Sesame Street',
            ],
        ], $actual);
    }

    /** @test */
    public function it_can_return_multiple_resources_applying_an_array()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $actual = Jory::multiple()
            ->applyArray([
                'user:first' => [
                    'fld' => 'name',
                ],
                'team:first' => [
                    'fld' => 'name',
                ],
            ])
            ->toArray();

        $this->assertEquals([
            'user:first' => [
                'name' => 'Bert',
            ],
            'team:first' => [
                'name' => 'Sesame Street',
            ],
        ], $actual);
    }

    /** @test */
    public function it_can_return_multiple_resources_applying_an_array_using_apply()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $actual = Jory::multiple()
            ->apply([
                'user:first' => [
                    'fld' => 'name',
                ],
                'team:first' => [
                    'fld' => 'name',
                ],
            ])
            ->toArray();

        $this->assertEquals([
            'user:first' => [
                'name' => 'Bert',
            ],
            'team:first' => [
                'name' => 'Sesame Street',
            ],
        ], $actual);
    }

    /** @test */
    public function it_throws_an_exception_when_invalid_data_is_applied()
    {
        $this->expectException(LaravelJoryException::class);
        $this->expectExceptionMessage('Unexpected type given. Please provide an array or Json string.');

        Jory::multiple()->apply(new \stdClass());
    }
}
