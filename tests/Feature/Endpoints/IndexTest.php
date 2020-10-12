<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Endpoints;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function it_can_return_multiple_records_using_the_index_route()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
            ],
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird'],
                ['name' => 'Cookie Monster'],
                ['name' => 'Ernie'],
                ['name' => 'Oscar'],
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_can_return_multiple_records_using_the_index_route_with_more_jory_data_applied()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%e%',
                ],
                'srt' => '-name',
                'lmt' => 2,
                'rlt' => [
                    'team' => [
                        'fld' => 'name',
                    ]
                ]
            ],
        ])->assertExactJson([
            'data' => [
                [
                    'name' => 'The Count',
                    'team' => [
                        'name' => 'Sesame Street'
                    ]
                ],
                [
                    'name' => 'Ernie',
                    'team' => [
                        'name' => 'Sesame Street'
                    ]
                ],
            ],
        ]);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_collection()
    {
        Jory::register(TeamJoryResource::class);
        $this->json('GET', 'jory/team', [
            'jory' => '}',
        ])->assertStatus(422)->assertExactJson([
            'errors' => [
                'Jory string is no valid json.',
            ],
        ]);
    }

    /** @test */
    public function it_returns_a_single_error_when_a_jory_exception_is_thrown_loading_a_collection_2()
    {
        Jory::register(UserJoryResource::class);
        $this->json('GET', 'jory/user', [
            'jory' => '{"flt":{"f":"name","o":"=","d":"Ernie"},"rlt":{"team":{"flt":{"wrong":"parameter"}}}}',
        ])->assertStatus(422)
            ->assertExactJson([
            'errors' => [
                'Unknown key "wrong" in Jory Query. (Location: team.flt)',
            ],
        ]);
    }

    /** @test */
    public function it_returns_a_404_when_an_unknown_resource_is_requested()
    {
        $this->json('GET', 'jory/team')->assertStatus(404);
    }

    /** @test */
    public function a_collection_can_be_fetched_when_fetching_multiple_resources()
    {
        $this->seedSesameStreet();
        $this->seedSimpsons();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'team' => [
                    'fld' => 'name',
                ],
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'team' => [
                    ['name' => 'Sesame Street'],
                    ['name' => 'Simpsons'],
                ],
            ],
        ]);
    }
}
