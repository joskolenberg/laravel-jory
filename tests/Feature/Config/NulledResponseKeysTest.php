<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Config;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class NulledResponseKeysTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('jory.response.data_key', null);
        $app['config']->set('jory.response.errors_key', null);
    }

    /** @test */
    public function it_can_return_data_in_the_root_when_data_key_is_configured_null()
    {
        Jory::register(TeamJoryResource::class);
        $team = $this->seedSesameStreet();

        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name'
            ],
        ]);

        $expected = [
            'name' => 'Sesame Street',
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_return_data_for_multiple_resource_in_the_root_when_data_key_is_configured_null()
    {
        Jory::register(TeamJoryResource::class);
        $team = $this->seedSesameStreet();

        $response = $this->json('GET', 'jory', [
            'jory' => [
                'team:' . $team->id . ' as sesamestreet' => [
                    'fld' => 'name',
                ]
            ],
        ]);

        $expected = [
            'sesamestreet' => [
                'name' => 'Sesame Street',
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_return_errors_in_the_root_when_data_key_is_configured_null()
    {
        Jory::register(TeamJoryResource::class);
        $response = $this->json('GET', 'jory/team/3', [
            'jory' => [
                "fld" => "naame"
            ]
        ]);

        $expected = [
            'Field "naame" is not available, did you mean "name"? (Location: fld.naame)',
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }

    /** @test */
    public function it_can_return_errors_in_the_root_when_fetching_multiple_resources_and_data_key_is_configured_null()
    {
        Jory::register(TeamJoryResource::class);
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'team:1 as sesamestreet' => [
                    'fld' => 'naame',
                ],
                'team:2 as simpsons' => [
                    'fld' => 'jd',
                ],
            ],
        ]);

        $expected = [
            'team:1 as sesamestreet: Field "naame" is not available, did you mean "name"? (Location: fld.naame)',
            'team:2 as simpsons: Field "jd" is not available, did you mean "id"? (Location: fld.jd)',
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);
    }
}
