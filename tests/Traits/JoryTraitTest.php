<?php

namespace JosKolenberg\LaravelJory\Tests\Traits;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Tests\TestCase;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\AlbumJoryBuilder;

class JoryTraitTest extends TestCase
{
    /** @test */
    public function it_can_give_a_JoryBuilder_when_applied()
    {
        $this->assertInstanceOf(JoryBuilder::class, Person::jory());
    }

    /** @test */
    public function it_can_give_a_custom_JoryBuilder_when_applied_and_overridden()
    {
        $this->assertInstanceOf(AlbumJoryBuilder::class, Album::jory());
    }

    /** @test */
    public function it_can_define_a_route()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => '{"filter":{"f":"name","o":"like","v":"%in%"}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            [
                'id' => 1,
                'name' => 'Rolling Stones',
                'year_start' => 1962,
                'year_end' => null,
            ],
            [
                'id' => 2,
                'name' => 'Led Zeppelin',
                'year_start' => 1968,
                'year_end' => 1980,
            ],
        ]);
    }

    /** @test */
    public function it_can_define_a_route_for_a_custom_builder()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => '{"filter":{"f":"number_of_songs","o":">","v":10}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            [
                'id' => 3,
                'band_id' => 1,
                'name' => 'Exile on main st.',
                'release_date' => '1972-05-12',
            ],
            [
                'id' => 7,
                'band_id' => 3,
                'name' => 'Sgt. Peppers lonely hearts club band',
                'release_date' => '1967-06-01',
            ],
            [
                'id' => 8,
                'band_id' => 3,
                'name' => 'Abbey road',
                'release_date' => '1969-09-26',
            ],
            [
                'id' => 9,
                'band_id' => 3,
                'name' => 'Let it be',
                'release_date' => '1970-05-08',
            ],
            [
                'id' => 10,
                'band_id' => 4,
                'name' => 'Are you experienced',
                'release_date' => '1967-05-12',
            ],
            [
                'id' => 11,
                'band_id' => 4,
                'name' => 'Axis: Bold as love',
                'release_date' => '1967-12-01',
            ],
            [
                'id' => 12,
                'band_id' => 4,
                'name' => 'Electric ladyland',
                'release_date' => '1968-10-16',
            ],
        ]);
    }
}
