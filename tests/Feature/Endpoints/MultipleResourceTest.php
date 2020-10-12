<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Endpoints;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class MultipleResourceTest extends TestCase
{

    /** @test */
    public function it_can_load_multiple_resources_at_once()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'team' => [
                    'fld' => 'name',
                ],
                'user' => [
                    'fld' => 'name',
                ],
            ],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'team' => [
                    ['name' => 'Sesame Street'],
                ],
                'user' => [
                    ['name' => 'Bert'],
                    ['name' => 'Big Bird'],
                    ['name' => 'Cookie Monster'],
                    ['name' => 'Ernie'],
                    ['name' => 'Oscar'],
                    ['name' => 'The Count'],
                ],
            ]
        ]);
    }

    /** @test */
    public function it_returns_an_error_when_a_resource_is_not_found()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'tea' => [
                    'fld' => 'name',
                ],
                'user' => [
                    'fld' => 'name',
                ],
                'songwriter' => [
                    'fld' => 'name',
                ],
            ],
        ])->assertStatus(422)->assertExactJson([
            'errors' => [
                'Resource tea not found, did you mean "team"?',
                'Resource songwriter not found, no suggestions found.',
            ],
        ]);
    }

    /** @test */
    public function it_returns_an_error_when_a_JoryException_has_occured()
    {
        $this->json('GET', 'jory', [
            'jory' => '{',
        ])->assertStatus(422)->assertExactJson([
            'errors' => [
                'Jory string is no valid json.',
            ],
        ]);
    }

    /** @test */
    public function it_returns_an_error_when_a_LaravelJoryCallException_has_occured()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory', [
            'jory' => [
                'team' => [
                    'flt' => [
                        'f' => 'naame'
                    ],
                ],
                'user' => [
                    'srt' => ['naame']
                ],
            ],
        ])->assertStatus(422)->assertExactJson([
            'errors' => [
                'team: Field "naame" is not available for filtering, did you mean "name"? (Location: flt(naame))',
                'user: Field "naame" is not available for sorting, did you mean "name"? (Location: srt.naame)',
            ],
        ]);
    }
}
