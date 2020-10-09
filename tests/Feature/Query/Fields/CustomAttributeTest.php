<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Query\Fields;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\Feature\Query\Fields\JoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class CustomAttributeTest extends TestCase
{
    /** @test */
    public function it_can_get_a_custom_attribute_for_a_model()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);

        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => [
                    'name',
                    'users_string'
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users_string' => 'Bert, Big Bird, Cookie Monster, Ernie, Oscar, The Count',
            ],
        ]);
    }
}
