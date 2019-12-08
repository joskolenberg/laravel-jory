<?php

namespace JosKolenberg\LaravelJory\Tests;

class ConfigTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('jory.response.data-key', null);
        $app['config']->set('jory.response.errors-key', null);
    }

    /** @test */
    public function it_can_return_data_in_the_root_when_data_key_is_configured_null()
    {
        $response = $this->json('GET', 'jory/band/3', [
            'jory' => '{"fld":["name"]}',
        ]);

        $expected = [
            'name' => 'Beatles',
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_data_in_the_root_when_data_key_is_configured_null_2()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => '{"band:3 as beatles":{"fld":["name"]}}',
        ]);

        $expected = [
            'beatles' => [
                'name' => 'Beatles',
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_return_errors_in_the_root_when_data_key_is_configured_null()
    {
        $response = $this->json('GET', 'jory/band/3', [
            'jory' => '{"fld":["naame"]}',
        ]);

        $expected = [
            'Field "naame" is not available, did you mean "name"? (Location: fields.naame)',
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_return_errors_in_the_root_when_data_key_is_configured_null_2()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => '{"band:3 as beatles":{"fld":["naame"]}}',
        ]);

        $expected = [
            'band:3 as beatles: Field "naame" is not available, did you mean "name"? (Location: fields.naame)',
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function a_relation_can_be_defined_with_a_custom_jory_resource_1()
    {
        $response = $this->json('GET', 'jory/album/5', [
            'jory' => [
                'fld' => ['name'],
                'rlt' => [
                    'customSongs2 as songs' => [
                        'flt' => [
                            'f' => 'title',
                            'o' => 'like',
                            'd' => '%love%',
                        ],
                        'fld' => [
                            'custom_field',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'name' => 'Led Zeppelin II',
            'songs' => [
                [
                    'custom_field' => 'custom_value',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function a_relation_can_be_defined_with_a_custom_jory_resource_2()
    {
        $response = $this->json('GET', 'jory/album/5', [
            'jory' => [
                'fld' => ['name'],
                'rlt' => [
                    'customSongs3 as songs' => [
                        'flt' => [
                            'f' => 'title',
                            'o' => 'like',
                            'd' => '%love%',
                        ],
                        'fld' => [
                            'title',
                            'custom_field',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'Field "custom_field" is not available, no suggestions found. (Location: customSongs3 as songs.fields.custom_field)',
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function a_relation_can_be_defined_with_a_custom_jory_resource_3()
    {
        $response = $this->json('GET', 'jory/album/5', [
            'jory' => [
                'fld' => ['name'],
                'rlt' => [
                    'customSongs3' => [
                        'flt' => [
                            'f' => 'title',
                            'o' => 'like',
                            'd' => '%love%',
                        ],
                        'fld' => [
                            'title',
                            'custom_field',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = [
            'Field "custom_field" is not available, no suggestions found. (Location: customSongs3.fields.custom_field)',
        ];
        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }
}
