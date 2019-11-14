<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\CustomSongJoryResource;

class CustomAttributeTest extends TestCase
{
    /** @test */
    public function it_can_get_a_custom_attribute_for_a_model()
    {
        Jory::register(CustomSongJoryResource::class);

        $response = $this->json('GET', 'jory/song/12', [
            'jory' => [
                'fld' => [
                    'title',
                    'description'
                ]
            ],
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'title' => 'Wild Horses',
                'description' => 'Wild Horses from the Sticky Fingers album.',
            ],
        ]);

        $this->assertQueryCount(2);
    }
}
