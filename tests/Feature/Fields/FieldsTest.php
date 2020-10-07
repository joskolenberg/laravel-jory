<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Fields;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\Fields\JoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class FieldsTest extends TestCase
{
    /** @test */
    public function it_can_specify_the_fields_to_return()
    {
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();

        $response = $this->json('GET', 'jory/team', [
            'jory' => [
                'fld' => [
                    'name',
                ],
            ]
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'name' => 'Sesame Street',
                ],
                [
                    'name' => 'Simpsons',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_specify_the_fields_to_return_on_a_relation()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(\JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource::class);
        $team = $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => ['name'],
                'rlt' => [
                    'users' => [
                        'fld' => 'name',
                    ]
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users' => [
                    [
                        'name' => 'Bert',
                    ],
                    [
                        'name' => 'Big Bird',
                    ],
                    [
                        'name' => 'Cookie Monster',
                    ],
                    [
                        'name' => 'Ernie',
                    ],
                    [
                        'name' => 'Oscar',
                    ],
                    [
                        'name' => 'The Count',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function when_the_fields_parameter_is_an_empty_array_no_fields_will_be_returned()
    {
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();

        $response = $this->json('GET', 'jory/team', [
            'jory' => [
                'fld' => [],
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [],
                [],
            ],
        ]);
    }

    /** @test */
    public function it_can_return_custom_model_attributes()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'custom_value',
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'custom_value' => 'custom value',
                ],
                [
                    'custom_value' => 'custom value',
                ],
                [
                    'custom_value' => 'custom value',
                ],
                [
                    'custom_value' => 'custom value',
                ],
                [
                    'custom_value' => 'custom value',
                ],
                [
                    'custom_value' => 'custom value',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_use_an_asterisk_to_select_all_fields_configured_in_the_jory_resource()
    {
        Jory::register(TeamJoryResource::class);
        $team = $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => '*',
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'id' => 1,
                'name' => 'Sesame Street',
            ],
        ]);
    }

    /** @test */
    public function it_can_use_an_asterisk_in_array_notation_to_select_all_fields_configured_in_the_jory_resource()
    {
        Jory::register(TeamJoryResource::class);
        $team = $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => ['*'],
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'id' => 1,
                'name' => 'Sesame Street',
            ],
        ]);
    }

    /** @test */
    public function it_returns_no_fields_when_the_fields_parameter_is_omitted()
    {
        Jory::register(TeamJoryResource::class);
        $team = $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [],
        ]);
    }

    /** @test */
    public function it_returns_no_fields_when_the_fields_parameter_is_an_empty_array()
    {
        Jory::register(TeamJoryResource::class);
        $team = $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [],
        ]);
    }
}
