<?php

namespace JosKolenberg\LaravelJory\Tests\Traits;

use JosKolenberg\LaravelJory\GenericJoryBuilder;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\TestCase;

class JoryTraitTest extends TestCase
{


    protected function setUp()
    {
        parent::setUp();

        Band::joryRoutes('band');
    }

    /** @test */
    public function it_can_give_a_genericJoryBuilder_when_applied()
    {
        $this->assertInstanceOf(GenericJoryBuilder::class, Album::jory());
    }

    /** @test */
    public function it_can_define_a_route()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"filter":{"f":"name","o":"like","v":"%in%"}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id'   => 1,
                    'name' => 'Rolling Stones',
                ],
                [
                    'id'   => 2,
                    'name' => 'Led Zeppelin',
                ],
            ]);
    }

}
