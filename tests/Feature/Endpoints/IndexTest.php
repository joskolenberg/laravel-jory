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

}
