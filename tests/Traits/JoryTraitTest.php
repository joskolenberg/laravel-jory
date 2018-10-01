<?php

namespace JosKolenberg\LaravelJory\Tests\Traits;

use JosKolenberg\LaravelJory\CustomJoryBuilder;
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
        Album::joryRoutes('album');
    }

    /** @test */
    public function it_can_give_a_genericJoryBuilder_when_applied()
    {
        $this->assertInstanceOf(GenericJoryBuilder::class, Band::jory());
    }

    /** @test */
    public function it_can_give_a_customJoryBuilder_when_applied_and_overridden()
    {
        $this->assertInstanceOf(CustomJoryBuilder::class, Album::jory());
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
                    'id' => 1,
                    'name' => 'Rolling Stones',
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                ],
            ]);
    }

    /** @test */
    public function it_can_define_a_route_for_a_custom_builder()
    {
        $response = $this->json('GET', '/album', [
            'jory' => '{"filter":{"f":"number_of_songs","o":">","v":10}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => 3,
                    'name' => 'Exile on main st.',
                ],
                [
                    'id' => 7,
                    'name' => 'Sgt. Peppers lonely hearts club band',
                ],
                [
                    'id' => 8,
                    'name' => 'Abbey road',
                ],
                [
                    'id' => 9,
                    'name' => 'Let it be',
                ],
                [
                    'id' => 10,
                    'name' => 'Are you experienced',
                ],
                [
                    'id' => 11,
                    'name' => 'Axis: Bold as love',
                ],
                [
                    'id' => 12,
                    'name' => 'Electric ladyland',
                ],
            ]);
    }

}
