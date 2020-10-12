<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Endpoints;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class CountTest extends TestCase
{

    /** @test */
    public function it_can_return_the_record_count()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/count')
            ->assertStatus(200)->assertExactJson([
            'data' => 6,
        ]);
    }

    /** @test */
    public function it_can_return_the_record_count_and_should_ignore_pagination()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/count', [
            'jory' => [
                'ofs' => 2,
                'lmt' => 1,
            ],
        ])->assertExactJson([
            'data' => 6,
        ]);
    }

    /** @test */
    public function it_can_return_the_record_count_with_a_filter_applied()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/count', [
            'jory' => [
                'ofs' => 2,
                'lmt' => 1,
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%e%',
                ]
            ],
        ])->assertExactJson([
            'data' => 4,
        ]);
    }

    /** @test */
    public function it_returns_an_error_when_a_jory_exception_is_thrown_loading_a_count()
    {
        Jory::register(UserJoryResource::class);
        $this->json('GET', 'jory/user/count', [
            'jory' => '}',
        ])->assertStatus(422)
            ->assertExactJson([
            'errors' => [
                'Jory string is no valid json.',
            ],
        ]);
    }

    /** @test */
    public function it_returns_a_404_when_an_unknown_resource_count_is_requested()
    {
        $this->json('GET', 'jory/team/count')->assertStatus(404);
    }

    /** @test */
    public function a_count_can_be_done_when_fetching_multiple_resources()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'team:count' => [],
                'user:count' => [
                    'flt' => [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%e%',
                    ]
                ],
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'team:count' => 1,
                'user:count' => 4,
            ],
        ]);
    }

    /** @test */
    public function a_count_can_be_done_on_relations()
    {
        $team = $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'users:count' => [
                        'flt' => [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%e%',
                        ]
                    ],
                ],
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users:count' => 4,
            ],
        ]);
    }
}
