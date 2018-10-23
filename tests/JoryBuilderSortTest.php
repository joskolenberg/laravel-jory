<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Album;

class JoryBuilderSortTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Band::joryRoutes('band');
        Album::joryRoutes('album');
    }

    /** @test */
    public function it_can_sort_a_query_ascending()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"srt":{"name":"asc"}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                ],
            ]);
    }

    /** @test */
    public function it_can_sort_a_query_descending()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"srt":{"name":"desc"}}',
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
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                ],
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                ],
            ]);
    }

    /** @test */
    public function it_can_sort_a_query_on_multiple_fields_1()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"srt":{"year_end":"desc","name":"asc"}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                ],
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                ],
            ]);
    }

    /** @test */
    public function it_can_sort_a_query_on_multiple_fields_2()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"srt":{"year_end":"desc","name":"desc"}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                ],
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                ],
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                ],
            ]);
    }

    /** @test */
    public function it_can_sort_a_query_on_multiple_fields_3()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"srt":{"year_end":"asc","name":"asc"}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                ],
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
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
    public function it_can_sort_a_query_on_multiple_fields_4()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"srt":{"year_end":"asc","name":"desc"}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                ],
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
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
    public function it_can_sort_a_relation()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"srt":{"name":"asc"},"rlt":{"people":{"srt":{"last_name":"asc"}}}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'year_start' => 1960,
                    'year_end' => 1970,
                    'people' => [
                        [
                            'id' => 11,
                            'first_name' => 'George',
                            'last_name' => 'Harrison',
                            'date_of_birth' => '1943-02-24',
                            'full_name' => 'George Harrison',
                        ],
                        [
                            'id' => 9,
                            'first_name' => 'John',
                            'last_name' => 'Lennon',
                            'date_of_birth' => '1940-10-09',
                            'full_name' => 'John Lennon',
                        ],
                        [
                            'id' => 10,
                            'first_name' => 'Paul',
                            'last_name' => 'McCartney',
                            'date_of_birth' => '1942-06-18',
                            'full_name' => 'Paul McCartney',
                        ],
                        [
                            'id' => 12,
                            'first_name' => 'Ringo',
                            'last_name' => 'Starr',
                            'date_of_birth' => '1940-07-07',
                            'full_name' => 'Ringo Starr',
                        ],
                    ],
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'year_start' => 1966,
                    'year_end' => 1970,
                    'people' => [
                        [
                            'id' => 13,
                            'first_name' => 'Jimi',
                            'last_name' => 'Hendrix',
                            'date_of_birth' => '1942-11-27',
                            'full_name' => 'Jimi Hendrix',
                        ],
                        [
                            'id' => 15,
                            'first_name' => 'Mitch',
                            'last_name' => 'Mitchell',
                            'date_of_birth' => '1946-07-09',
                            'full_name' => 'Mitch Mitchell',
                        ],
                        [
                            'id' => 14,
                            'first_name' => 'Noel',
                            'last_name' => 'Redding',
                            'date_of_birth' => '1945-12-25',
                            'full_name' => 'Noel Redding',
                        ],
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                    'people' => [
                        [
                            'id' => 8,
                            'first_name' => 'John',
                            'last_name' => 'Bonham',
                            'date_of_birth' => '1948-05-31',
                            'full_name' => 'John Bonham',
                        ],
                        [
                            'id' => 7,
                            'first_name' => 'John Paul',
                            'last_name' => 'Jones',
                            'date_of_birth' => '1946-01-03',
                            'full_name' => 'John Paul Jones',
                        ],
                        [
                            'id' => 6,
                            'first_name' => 'Jimmy',
                            'last_name' => 'Page',
                            'date_of_birth' => '1944-01-09',
                            'full_name' => 'Jimmy Page',
                        ],
                        [
                            'id' => 5,
                            'first_name' => 'Robert',
                            'last_name' => 'Plant',
                            'date_of_birth' => '1948-08-20',
                            'full_name' => 'Robert Plant',
                        ],
                    ],
                ],
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                    'people' => [
                        [
                            'id' => 1,
                            'first_name' => 'Mick',
                            'last_name' => 'Jagger',
                            'date_of_birth' => '1943-07-26',
                            'full_name' => 'Mick Jagger',
                        ],
                        [
                            'id' => 2,
                            'first_name' => 'Keith',
                            'last_name' => 'Richards',
                            'date_of_birth' => '1943-12-18',
                            'full_name' => 'Keith Richards',
                        ],
                        [
                            'id' => 4,
                            'first_name' => 'Charlie',
                            'last_name' => 'Watts',
                            'date_of_birth' => '1941-06-02',
                            'full_name' => 'Charlie Watts',
                        ],
                        [
                            'id' => 3,
                            'first_name' => 'Ronnie',
                            'last_name' => 'Wood',
                            'date_of_birth' => '1947-06-01',
                            'full_name' => 'Ronnie Wood',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_combine_relations_filters_and_sorts()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"flt":{"f":"name","o":"like","v":"%in%"},"srt":{"name":"desc"},"rlt":{"people":{"flt":{"f":"last_name","o":"like","v":"%a%"},"srt":{"last_name":"desc"},"fld":["last_name"]}}}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                    'people' => [
                        [
                            'last_name' => 'Watts',
                        ],
                        [
                            'last_name' => 'Richards',
                        ],
                        [
                            'last_name' => 'Jagger',
                        ],
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                    'people' => [
                        [
                            'last_name' => 'Plant',
                        ],
                        [
                            'last_name' => 'Page',
                        ],
                        [
                            'last_name' => 'Bonham',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_apply_a_custom_sort()
    {
        $response = $this->json('GET', '/album', [
            'jory' => '{"srt":{"number_of_songs":"asc","name":"asc"},"fld":["id","name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 4,
                    'name' => 'Led Zeppelin',
                ],
                [
                    'id' => 5,
                    'name' => 'Led Zeppelin II',
                ],
                [
                    'id' => 1,
                    'name' => 'Let it bleed',
                ],
                [
                    'id' => 6,
                    'name' => 'Led Zeppelin III',
                ],
                [
                    'id' => 2,
                    'name' => 'Sticky Fingers',
                ],
                [
                    'id' => 10,
                    'name' => 'Are you experienced',
                ],
                [
                    'id' => 9,
                    'name' => 'Let it be',
                ],
                [
                    'id' => 11,
                    'name' => 'Axis: Bold as love',
                ],
                [
                    'id' => 7,
                    'name' => 'Sgt. Peppers lonely hearts club band',
                ],
                [
                    'id' => 12,
                    'name' => 'Electric ladyland',
                ],
                [
                    'id' => 8,
                    'name' => 'Abbey road',
                ],
                [
                    'id' => 3,
                    'name' => 'Exile on main st.',
                ],
            ]);
    }

    /** @test */
    public function it_can_apply_a_custom_sort_2()
    {
        $response = $this->json('GET', '/album', [
            'jory' => '{"srt":{"band_name":"asc","number_of_songs":"asc"},"fld":["id","name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
                [
                    'id' => 9,
                    'name' => 'Let it be',
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
                [
                    'id' => 4,
                    'name' => 'Led Zeppelin',
                ],
                [
                    'id' => 5,
                    'name' => 'Led Zeppelin II',
                ],
                [
                    'id' => 6,
                    'name' => 'Led Zeppelin III',
                ],
                [
                    'id' => 1,
                    'name' => 'Let it bleed',
                ],
                [
                    'id' => 2,
                    'name' => 'Sticky Fingers',
                ],
                [
                    'id' => 3,
                    'name' => 'Exile on main st.',
                ],
            ]);
    }
}
