<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Album;

class GenericJoryBuilderSortTest extends TestCase
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

        $response
            ->assertStatus(200)
            ->assertJson([
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

        $response
            ->assertStatus(200)
            ->assertJson([
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

        $response
            ->assertStatus(200)
            ->assertJson([
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

        $response
            ->assertStatus(200)
            ->assertJson([
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

        $response
            ->assertStatus(200)
            ->assertJson([
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

        $response
            ->assertStatus(200)
            ->assertJson([
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

        $response
            ->assertStatus(200)
            ->assertJson([
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
                        ],
                        [
                            'id' => 9,
                            'first_name' => 'John',
                            'last_name' => 'Lennon',
                            'date_of_birth' => '1940-10-09',
                        ],
                        [
                            'id' => 10,
                            'first_name' => 'Paul',
                            'last_name' => 'McCartney',
                            'date_of_birth' => '1942-06-18',
                        ],
                        [
                            'id' => 12,
                            'first_name' => 'Ringo',
                            'last_name' => 'Starr',
                            'date_of_birth' => '1940-07-07',
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
                        ],
                        [
                            'id' => 15,
                            'first_name' => 'Mitch',
                            'last_name' => 'Mitchell',
                            'date_of_birth' => '1946-07-09',
                        ],
                        [
                            'id' => 14,
                            'first_name' => 'Noel',
                            'last_name' => 'Redding',
                            'date_of_birth' => '1945-12-25',
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
                        ],
                        [
                            'id' => 7,
                            'first_name' => 'John Paul',
                            'last_name' => 'Jones',
                            'date_of_birth' => '1946-01-03',
                        ],
                        [
                            'id' => 6,
                            'first_name' => 'Jimmy',
                            'last_name' => 'Page',
                            'date_of_birth' => '1944-01-09',
                        ],
                        [
                            'id' => 5,
                            'first_name' => 'Robert',
                            'last_name' => 'Plant',
                            'date_of_birth' => '1948-08-20',
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
                        ],
                        [
                            'id' => 2,
                            'first_name' => 'Keith',
                            'last_name' => 'Richards',
                            'date_of_birth' => '1943-12-18',
                        ],
                        [
                            'id' => 4,
                            'first_name' => 'Charlie',
                            'last_name' => 'Watts',
                            'date_of_birth' => '1941-06-02',
                        ],
                        [
                            'id' => 3,
                            'first_name' => 'Ronnie',
                            'last_name' => 'Wood',
                            'date_of_birth' => '1947-06-01',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_combine_relations_filters_and_sorts()
    {
        $response = $this->json('GET', '/band', [
            'jory' => '{"flt":{"f":"name","o":"like","v":"%in%"},"srt":{"name":"desc"},"rlt":{"people":{"flt":{"f":"last_name","o":"like","v":"%a%"},"srt":{"last_name":"desc"}}}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'year_start' => 1962,
                    'year_end' => null,
                    'people' => [
                        [
                            'id' => 4,
                            'first_name' => 'Charlie',
                            'last_name' => 'Watts',
                            'date_of_birth' => '1941-06-02',
                        ],
                        [
                            'id' => 2,
                            'first_name' => 'Keith',
                            'last_name' => 'Richards',
                            'date_of_birth' => '1943-12-18',
                        ],
                        [
                            'id' => 1,
                            'first_name' => 'Mick',
                            'last_name' => 'Jagger',
                            'date_of_birth' => '1943-07-26',
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
                            'id' => 5,
                            'first_name' => 'Robert',
                            'last_name' => 'Plant',
                            'date_of_birth' => '1948-08-20',
                        ],
                        [
                            'id' => 6,
                            'first_name' => 'Jimmy',
                            'last_name' => 'Page',
                            'date_of_birth' => '1944-01-09',
                        ],
                        [
                            'id' => 8,
                            'first_name' => 'John',
                            'last_name' => 'Bonham',
                            'date_of_birth' => '1948-05-31',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_apply_a_custom_sort()
    {
        $response = $this->json('GET', '/album', [
            'jory' => '{"srt":{"number_of_songs":"asc","name":"asc"}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => 4,
                    'band_id' => 2,
                    'name' => 'Led Zeppelin',
                    'release_date' => '1969-01-12',
                ],
                [
                    'id' => 5,
                    'band_id' => 2,
                    'name' => 'Led Zeppelin II',
                    'release_date' => '1969-10-22',
                ],
                [
                    'id' => 1,
                    'band_id' => 1,
                    'name' => 'Let it bleed',
                    'release_date' => '1969-12-05',
                ],
                [
                    'id' => 6,
                    'band_id' => 2,
                    'name' => 'Led Zeppelin III',
                    'release_date' => '1970-10-05',
                ],
                [
                    'id' => 2,
                    'band_id' => 1,
                    'name' => 'Sticky Fingers',
                    'release_date' => '1971-04-23',
                ],
                [
                    'id' => 10,
                    'band_id' => 4,
                    'name' => 'Are you experienced',
                    'release_date' => '1967-05-12',
                ],
                [
                    'id' => 9,
                    'band_id' => 3,
                    'name' => 'Let it be',
                    'release_date' => '1970-05-08',
                ],
                [
                    'id' => 11,
                    'band_id' => 4,
                    'name' => 'Axis: Bold as love',
                    'release_date' => '1967-12-01',
                ],
                [
                    'id' => 7,
                    'band_id' => 3,
                    'name' => 'Sgt. Peppers lonely hearts club band',
                    'release_date' => '1967-06-01',
                ],
                [
                    'id' => 12,
                    'band_id' => 4,
                    'name' => 'Electric ladyland',
                    'release_date' => '1968-10-16',
                ],
                [
                    'id' => 8,
                    'band_id' => 3,
                    'name' => 'Abbey road',
                    'release_date' => '1969-09-26',
                ],
                [
                    'id' => 3,
                    'band_id' => 1,
                    'name' => 'Exile on main st.',
                    'release_date' => '1972-05-12',
                ],
            ]);
    }

    /** @test */
    public function it_can_apply_a_custom_sort_2()
    {
        $response = $this->json('GET', '/album', [
            'jory' => '{"srt":{"band_name":"asc","number_of_songs":"asc"}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => 9,
                    'band_id' => 3,
                    'name' => 'Let it be',
                    'release_date' => '1970-05-08',
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
                [
                    'id' => 4,
                    'band_id' => 2,
                    'name' => 'Led Zeppelin',
                    'release_date' => '1969-01-12',
                ],
                [
                    'id' => 5,
                    'band_id' => 2,
                    'name' => 'Led Zeppelin II',
                    'release_date' => '1969-10-22',
                ],
                [
                    'id' => 6,
                    'band_id' => 2,
                    'name' => 'Led Zeppelin III',
                    'release_date' => '1970-10-05',
                ],
                [
                    'id' => 1,
                    'band_id' => 1,
                    'name' => 'Let it bleed',
                    'release_date' => '1969-12-05',
                ],
                [
                    'id' => 2,
                    'band_id' => 1,
                    'name' => 'Sticky Fingers',
                    'release_date' => '1971-04-23',
                ],
                [
                    'id' => 3,
                    'band_id' => 1,
                    'name' => 'Exile on main st.',
                    'release_date' => '1972-05-12',
                ],
            ]);
    }
}
