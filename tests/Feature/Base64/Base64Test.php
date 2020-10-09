<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Base64;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class Base64Test extends TestCase
{
    /** @test */
    public function it_can_process_a_base64_encoded_json_string()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();

        $response = $this->json('GET', 'jory/team', [
            'jory' => base64_encode(json_encode([
                'fld' => 'name',
                'flt' =>
                    [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%imp%',
                    ],
                'rlt' =>
                    [
                        'users' =>
                            [
                                'fld' =>
                                    [
                                        'name',
                                    ],
                                'flt' =>
                                    [
                                        'f' => 'name',
                                        'o' => 'like',
                                        'd' => '%h%',
                                    ],
                            ],
                    ],
            ])),
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Simpsons',
                    'users' => [
                        [
                            'name' => 'Homer',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_process_a_base64_encoded_json_string_for_multiple_resources()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();

        $response = $this->json('GET', 'jory', [
            'jory' => base64_encode(json_encode([
                'user:first as bert' => [
                    'fld' => [
                        'name',
                        'team.name',
                    ],
                ],
                'team:first as simpsons' => [
                    'fld' => 'name',
                    'flt' => [
                        'f' => 'name',
                        'o' => 'like',
                        'd' => '%simp%',
                    ],
                    'rlt' => [
                        'users:count as users_count' => [],
                    ]
                ]
            ])),
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'bert' => [
                    'name' => 'Bert',
                    'team' => [
                        'name' => 'Sesame Street',
                    ],
                ],
                'simpsons' => [
                    'name' => 'Simpsons',
                    'users_count' => 5,
                ],
            ],
        ]);
    }
}
