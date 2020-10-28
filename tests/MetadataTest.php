<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\User;

class MetadataTest extends TestCase
{

    /** @test */
    public function it_can_return_the_meta_data_in_camelCase()
    {
        $response = $this->json('GET', 'jory/band/1', [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'albums:count' => [],
                    'songs:count' => [],
                ]
            ],
            'meta' => ['queryCount'],
            'case' => 'camel'
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => "Rolling Stones",
                'albums:count' => 3,
                'songs:count' => 37,
            ],
            'meta' => [
                'queryCount' => 3
            ]
        ]);

        $this->assertQueryCount(3);
    }

}
