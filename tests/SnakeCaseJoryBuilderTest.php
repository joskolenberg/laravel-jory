<?php

namespace JosKolenberg\LaravelJory\Tests;

class SnakeCaseJoryBuilderTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('jory.case', 'camel');
        $app['config']->set('jory.request.case-key', 'case_key');
    }

    /** @test */
    public function it_returns_the_configs_default_fields_in_snake_case()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'case_key' => 'snake',
        ]);

        $expected = [
            'data' => [
                'id' => 2,
                'name' => 'Led Zeppelin',
                'year_start' => 1968,
                'year_end' => 1980,
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_returns_the_toarray_default_fields_in_snake_case()
    {
        $response = $this->json('GET', 'jory/song/2', [
            'case_key' => 'snake',
        ]);

        $expected = [
            'data' => [
                'id' => 2,
                'album_id' => 1,
                'title' => 'Love In Vain (Robert Johnson)',
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_returns_the_defined_fields_in_snake_case()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'case_key' => 'snake',
            'jory' => '{"fld":["year_start","id","year_end"]}',
        ]);

        $expected = [
            'data' => [
                'id' => 2,
                'year_start' => 1968,
                'year_end' => 1980,
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_validates_the_fields_in_snake_case()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'case_key' => 'snake',
            'jory' => '{"fld":["yearStart","id","year_end"]}',
        ]);

        $expected = [
            'errors' => [
                'Field "yearStart" is not available, did you mean "year_start"? (Location: fields.yearStart)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_apply_default_filters_in_snakecase()
    {
        $response = $this->json('GET', 'jory/band', [
            'case_key' => 'snake',
            'jory' => '{"flt":{"f":"year_start","d":1968}}',
        ]);

        $expected = [
            'data' => [
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'year_start' => 1968,
                    'year_end' => 1980,
                ],
            ],
        ];

        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_custom_filters_in_snakecase()
    {
        $response = $this->json('GET', 'jory/album', [
            'case_key' => 'snake',
            'jory' => '{"flt":{"f":"number_of_songs","o":">=","d":15},"srt":["id"]}',
        ]);

        $expected = [
            'data' => [
                [
                    'id' => 3,
                    'band_id' => 1,
                    'name' => 'Exile on main st.',
                    'release_date' => '1972-05-12',
                ],
                [
                    'id' => 8,
                    'band_id' => 3,
                    'name' => 'Abbey road',
                    'release_date' => '1969-09-26',
                ],
                [
                    'id' => 12,
                    'band_id' => 4,
                    'name' => 'Electric ladyland',
                    'release_date' => '1968-10-16',
                ],
            ],
        ];

        $response->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_validates_the_filters_in_snake_case()
    {
        $response = $this->json('GET', 'jory/album', [
            'case_key' => 'snake',
            'jory' => '{"flt":{"f":"numberOfSongs","o":">=","d":15}}',
        ]);

        $expected = [
            'errors' => [
                'Field "numberOfSongs" is not available for filtering, did you mean "number_of_songs"? (Location: filter(numberOfSongs))',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_apply_default_sorts_in_snakecase()
    {
        $response = $this->json('GET', 'jory/band', [
            'case_key' => 'snake',
            'jory' => '{"srt":["year_end","-year_start"]}',
        ]);

        $expected = [
            'data' => [
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
            ],
        ];

        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_custom_sorts_in_snakecase()
    {
        $response = $this->json('GET', 'jory/album', [
            'case_key' => 'snake',
            'jory' => '{"srt":["number_of_songs","-band_id"],"lmt":5,"offset":7}',
        ]);

        $expected = [
            'data' => [
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
            ],
        ];

        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_validates_the_sorts_in_snake_case()
    {
        $response = $this->json('GET', 'jory/album', [
            'case_key' => 'snake',
            'jory' => '{"srt":["numberOfSongs","-bandId"],"lmt":5,"offset":7}',
        ]);

        $expected = [
            'errors' => [
                'Field "numberOfSongs" is not available for sorting, did you mean "number_of_songs"? (Location: sorts.numberOfSongs)',
                'Field "bandId" is not available for sorting, did you mean "band_id"? (Location: sorts.bandId)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_return_relations_in_snakecase()
    {
        $response = $this->json('GET', 'jory/album/2', [
            'case_key' => 'snake',
            'jory' => '{"rlt":{"album_cover":{}},"fld":["name"]}',
        ]);

        $expected = [
            'data' => [
                'name' => 'Sticky Fingers',
                'album_cover' => [
                    'id' => 2,
                    'album_id' => 2,
                    'image' => '.........-#WWW@#####@#=+-:**--:------:@W@@W@#**##@WWWWWW++WWWWWW@+-#WWWWWWWWW@@
.........+WWWW@@@#=**=#+-*@===@@@@#@@@+@=@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@
........-#WWWWWW@##=#@#+-*@#*W@@##*==:*W#-#WWWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWWW#
........+W@@@W@#@=#@#@#=-+##*=#@#=:-:-@@+-@WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW+.
........:*==**=#*=*@@@@@++=@##@@#+:+-:W=.-#WWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWW:..
..--++:-:+++***:-.:+=*=#*+=@@@W@@+=*-*W@-:WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW@:.
:-.+*+=**:-:*==@#@WW=@WW#+:WWWWW@=+:-#@-.*WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW*..
+...:-:****===*=W@@=@WWW@WWWWWWWWWWWW#WWWW#WW@WW#@WWWWWWWWWWWWWWWWWWWWWWWWWW*..
-:=******+::+:+=---=WWWW@WWWWWWWWWWWWWWW#@###WWWWWWWWWWWWWWW=*@WWWWWWWWWWWWW:..
.+:+:-.-:***+*+:--+*=#=#@WWWWWWWWWWWWWW@*W#=@WWWWWWWWWWWWWWWWWWWWWW@WWWWWW@+...
..+:***=+++**+**#########@@WWWWWWWWWWW@*#@@#W@WWWWWWWWWWWWWWWWWW=W@:+#WWWW@:...
..:++*++::--::***#@@W@@@@@@WWWWWWWWWW@@@W@@WWWWWWWWWWWWWWWWWWW@##@WWWWW@#WWW+..
.......--:+**#####=######=##@WWWWWWWWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW=..
.....-::--:-:*=+==#@@@@WWWWWWWWWWWWWWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+..
....-*+*+:::++:-+**=#@@@WWWWWWWWWWWWWW#W@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW*...
.-:=:***+=#@@@#=*+**+=#@@WWWWWWWWWWWW@#@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW#...
..::*::***+*=#@W@@@@#=#@@@WWWWWWWWWWW@W@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW:..
....--*:*=*+-+=*#@W@@@@@@@@WWWWWWWWWWWW#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+..
......--===+-**+*+###@@@@#@WWWWWWWWWWW@#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@-.
.....---+###*:**-:*:*#**=@@WWWWWWWWWWW@#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+.
.......-.-=@#=++**:+*++:*##WWWWWWWWWWW#=WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@-
......-:*-.-+@#=++**::*+=@WWWWWWWWWW@:+@W@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW:
......*==@#+--=W#*-:***=#WWWWWWWWWWW=#@@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+
......-*=WWW@=-.:::+-+#WWWWWWWWWWWWW@#*==#@@@@WWWWWWWWWWWWWWWWWWWWWWWWWWW@@@@W:
......-++#WWWWW@---.-+=WWWWWWWWWWWWWWW@@+*=##@@WWWWWWWWWWWWWWWWWWWWWWWWWWW@@@W+
.......-:=WWWWWWW#--+#@WWWWWWWWWWWWWWW@+--:+=#=#@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-*@@WWWWWWWW=*=@WWWWWWWWWWWWWWWW@+++*###@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-:=@WWWWWWWWWWW=#WWWWWWWWWWWWW*WW=+++*=@@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW=
.......-+*=#@WWWWWWWWWWWWWWWWWWWWWWWW:@W@===*+**@@WWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-*@@@WWWWWWWWWWWWWWWWWWWWWWWW#.+W@+*==#@@@@@W@WWWWWWWWWWWWWWWWWWWWWWWWWW
......-:::@WWWWWWWWWWWWWWWWWWWWWWWWW*.:W@*=#*++*==#@@WWWWWWWWWWWWWWWWWWWWWWW@@@
.......-:*WWWWWWWWWWWWWWWWWWWWWWWWWW=.-@#+*++*#@****=@WWWWWWWWWWWWWWWWWWWWWWWWW
......-:=@WWWWWWWWWWWWWWWWWWWWWWWWWW:..#@:+:+**=**#@@#@@WWWWWWWWWWWWWWWWWWWWWWW
.....-:+=#WWWWWWWWWWWWWWWWWWWWWWWWW@...*@*+*++=#===####@@@@WWWWWWWWWWWWWWWWWWWW
......:+#@WWWWWWWWWWWWWWWWWWWWWWWWW+...:W*+:+*=**#@@@#=###@@@WWWWWWWWWWWWWWWWWW
.....-:+=@WWWWWWWWWWWWWWWWWWWWWWWW#....-@=**+=***=#**#WWWW@@W@WWWWWWWWWWWWWWWWW
......:*=@WWWWWWWWWWWWWWWWWWWWWWWW+.....#===+++*=+*#@@@#@@@WWWWWWWWWWWWWWWWWWWW
...--::*#WWWWWWWWWWWWWWWWWWWWWWWW@......**=##+**+**=*###@@@WWWWWWWWWWWWWWWWWWWW
...--+*=@WWWWWWWWWWWWWWWWWWWWWWWW+......-@===++*+***==*@@@@WWWWWWWWWWWWWWW@@@W@
...--+*#@WWWWWWWWWWWWWWWWWWWWWWW@-.......#=##==#======@#@@@@WWWWWWWWWWWWWW@@@@@',
                ],
            ],
        ];

        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_validates_the_relations_in_snake_case()
    {
        $response = $this->json('GET', 'jory/album', [
            'case_key' => 'snake',
            'jory' => '{"rlt":{"albumCover":{}},"fld":["name"]}',
        ]);

        $expected = [
            'errors' => [
                'Relation "albumCover" is not available, did you mean "album_cover"? (Location: relations.albumCover)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_return_multiple_records_in_snake_case()
    {
        $response = $this->json('GET', 'jory/band', [
            'case_key' => 'snake',
            'jory' => '{"lmt":2}',
        ]);

        $expected = [
            'data' => [
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
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_do_a_count_in_snake_case()
    {
        $response = $this->json('GET', 'jory/band/count', [
            'case_key' => 'snake',
            'jory' => '{"flt":{"f":"year_end","d":1970}}',
        ]);

        $expected = [
            'data' => 2,
        ];

        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_get_a_single_record_in_snake_case()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'case_key' => 'snake',
        ]);

        $expected = [
            'data' => [
                'id' => 2,
                'name' => 'Led Zeppelin',
                'year_start' => 1968,
                'year_end' => 1980,
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_show_the_options_for_the_resource_in_snake_case()
    {
        $response = $this->json('OPTIONS', 'jory/band', [
            'case_key' => 'snake',
        ]);

        $expected = [
            'fields' => [
                'id' => [
                    'description' => 'The id field.',
                    'default' => true,
                ],
                'name' => [
                    'description' => 'The name field.',
                    'default' => true,
                ],
                'year_start' => [
                    'description' => 'The year in which the band started.',
                    'default' => true,
                ],
                'year_end' => [
                    'description' => 'The year in which the band quitted, could be null if band still exists.',
                    'default' => true,
                ],
                'all_albums_string' => [
                    'description' => 'The all_albums_string field.',
                    'default' => false,
                ],
            ],
            'filters' => [
                'id' => [
                    'description' => 'Try this filter by id!',
                    'operators' => [
                        '=',
                        '>',
                        '<',
                        '<=',
                        '>=',
                        '<>',
                        '!=',
                    ],
                ],
                'name' => [
                    'description' => 'Filter on the name field.',
                    'operators' => [
                        '=',
                        '!=',
                        '<>',
                        '>',
                        '>=',
                        '<',
                        '<=',
                        '<=>',
                        'like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'year_start' => [
                    'description' => 'Filter on the year_start field.',
                    'operators' => [
                        '=',
                        '!=',
                        '<>',
                        '>',
                        '>=',
                        '<',
                        '<=',
                        '<=>',
                        'like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'year_end' => [
                    'description' => 'Filter on the year_end field.',
                    'operators' => [
                        '=',
                        '!=',
                        '<>',
                        '>',
                        '>=',
                        '<',
                        '<=',
                        '<=>',
                        'like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'has_album_with_name' => [
                    'description' => 'Filter bands that have an album with a given name.',
                    'operators' => [
                        '=',
                        '!=',
                        '<>',
                        '>',
                        '>=',
                        '<',
                        '<=',
                        '<=>',
                        'like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                'number_of_albums_in_year' => [
                    'description' => 'Filter the bands that released a given number of albums in a year, pass value and year parameter.',
                    'operators' => ['=', '>', '<', '<=', '>=', '<>', '!='],
                ],
            ],
            'sorts' => [
                'id' => [
                    'description' => 'Sort by the id field.',
                    'default' => false,
                ],
                'name' => [
                    'description' => 'Sort by the name field.',
                    'default' => false,
                ],
                'year_start' => [
                    'description' => 'Sort by the year_start field.',
                    'default' => false,
                ],
                'year_end' => [
                    'description' => 'Sort by the year_end field.',
                    'default' => false,
                ],
            ],
            'limit' => [
                'default' => 30,
                'max' => 120,
            ],
            'relations' => [
                'albums' => [
                    'description' => 'Get the related albums for the band.',
                    'type' => 'album',
                ],
                'people' => [
                    'description' => 'The people relation.',
                    'type' => 'person',
                ],
                'songs' => [
                    'description' => 'The songs relation.',
                    'type' => 'song',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_get_apply_snake_case_when_loading_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'case_key' => 'snake',
            'album_as_album_with_cover' => '{"rlt":{"album_cover":{}},"fld":["name"],"flt":{"f":"id","d":2}}',
            'album-cover_2_as_sticky_fingers_album_cover' => '{}',
        ]);

        $expected = [
            'data' => [
                'album_with_cover' => [
                    [
                        'name' => 'Sticky Fingers',
                        'album_cover' => [
                            'id' => 2,
                            'album_id' => 2,
                            'image' => '.........-#WWW@#####@#=+-:**--:------:@W@@W@#**##@WWWWWW++WWWWWW@+-#WWWWWWWWW@@
.........+WWWW@@@#=**=#+-*@===@@@@#@@@+@=@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@
........-#WWWWWW@##=#@#+-*@#*W@@##*==:*W#-#WWWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWWW#
........+W@@@W@#@=#@#@#=-+##*=#@#=:-:-@@+-@WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW+.
........:*==**=#*=*@@@@@++=@##@@#+:+-:W=.-#WWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWW:..
..--++:-:+++***:-.:+=*=#*+=@@@W@@+=*-*W@-:WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW@:.
:-.+*+=**:-:*==@#@WW=@WW#+:WWWWW@=+:-#@-.*WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW*..
+...:-:****===*=W@@=@WWW@WWWWWWWWWWWW#WWWW#WW@WW#@WWWWWWWWWWWWWWWWWWWWWWWWWW*..
-:=******+::+:+=---=WWWW@WWWWWWWWWWWWWWW#@###WWWWWWWWWWWWWWW=*@WWWWWWWWWWWWW:..
.+:+:-.-:***+*+:--+*=#=#@WWWWWWWWWWWWWW@*W#=@WWWWWWWWWWWWWWWWWWWWWW@WWWWWW@+...
..+:***=+++**+**#########@@WWWWWWWWWWW@*#@@#W@WWWWWWWWWWWWWWWWWW=W@:+#WWWW@:...
..:++*++::--::***#@@W@@@@@@WWWWWWWWWW@@@W@@WWWWWWWWWWWWWWWWWWW@##@WWWWW@#WWW+..
.......--:+**#####=######=##@WWWWWWWWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW=..
.....-::--:-:*=+==#@@@@WWWWWWWWWWWWWWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+..
....-*+*+:::++:-+**=#@@@WWWWWWWWWWWWWW#W@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW*...
.-:=:***+=#@@@#=*+**+=#@@WWWWWWWWWWWW@#@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW#...
..::*::***+*=#@W@@@@#=#@@@WWWWWWWWWWW@W@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW:..
....--*:*=*+-+=*#@W@@@@@@@@WWWWWWWWWWWW#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+..
......--===+-**+*+###@@@@#@WWWWWWWWWWW@#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@-.
.....---+###*:**-:*:*#**=@@WWWWWWWWWWW@#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+.
.......-.-=@#=++**:+*++:*##WWWWWWWWWWW#=WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@-
......-:*-.-+@#=++**::*+=@WWWWWWWWWW@:+@W@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW:
......*==@#+--=W#*-:***=#WWWWWWWWWWW=#@@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+
......-*=WWW@=-.:::+-+#WWWWWWWWWWWWW@#*==#@@@@WWWWWWWWWWWWWWWWWWWWWWWWWWW@@@@W:
......-++#WWWWW@---.-+=WWWWWWWWWWWWWWW@@+*=##@@WWWWWWWWWWWWWWWWWWWWWWWWWWW@@@W+
.......-:=WWWWWWW#--+#@WWWWWWWWWWWWWWW@+--:+=#=#@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-*@@WWWWWWWW=*=@WWWWWWWWWWWWWWWW@+++*###@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-:=@WWWWWWWWWWW=#WWWWWWWWWWWWW*WW=+++*=@@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW=
.......-+*=#@WWWWWWWWWWWWWWWWWWWWWWWW:@W@===*+**@@WWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-*@@@WWWWWWWWWWWWWWWWWWWWWWWW#.+W@+*==#@@@@@W@WWWWWWWWWWWWWWWWWWWWWWWWWW
......-:::@WWWWWWWWWWWWWWWWWWWWWWWWW*.:W@*=#*++*==#@@WWWWWWWWWWWWWWWWWWWWWWW@@@
.......-:*WWWWWWWWWWWWWWWWWWWWWWWWWW=.-@#+*++*#@****=@WWWWWWWWWWWWWWWWWWWWWWWWW
......-:=@WWWWWWWWWWWWWWWWWWWWWWWWWW:..#@:+:+**=**#@@#@@WWWWWWWWWWWWWWWWWWWWWWW
.....-:+=#WWWWWWWWWWWWWWWWWWWWWWWWW@...*@*+*++=#===####@@@@WWWWWWWWWWWWWWWWWWWW
......:+#@WWWWWWWWWWWWWWWWWWWWWWWWW+...:W*+:+*=**#@@@#=###@@@WWWWWWWWWWWWWWWWWW
.....-:+=@WWWWWWWWWWWWWWWWWWWWWWWW#....-@=**+=***=#**#WWWW@@W@WWWWWWWWWWWWWWWWW
......:*=@WWWWWWWWWWWWWWWWWWWWWWWW+.....#===+++*=+*#@@@#@@@WWWWWWWWWWWWWWWWWWWW
...--::*#WWWWWWWWWWWWWWWWWWWWWWWW@......**=##+**+**=*###@@@WWWWWWWWWWWWWWWWWWWW
...--+*=@WWWWWWWWWWWWWWWWWWWWWWWW+......-@===++*+***==*@@@@WWWWWWWWWWWWWWW@@@W@
...--+*#@WWWWWWWWWWWWWWWWWWWWWWW@-.......#=##==#======@#@@@@WWWWWWWWWWWWWW@@@@@',
                        ],
                    ],
                ],
                'sticky_fingers_album_cover' => [
                    'id' => 2,
                    'album_id' => 2,
                    'image' => '.........-#WWW@#####@#=+-:**--:------:@W@@W@#**##@WWWWWW++WWWWWW@+-#WWWWWWWWW@@
.........+WWWW@@@#=**=#+-*@===@@@@#@@@+@=@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@
........-#WWWWWW@##=#@#+-*@#*W@@##*==:*W#-#WWWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWWW#
........+W@@@W@#@=#@#@#=-+##*=#@#=:-:-@@+-@WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW+.
........:*==**=#*=*@@@@@++=@##@@#+:+-:W=.-#WWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWW:..
..--++:-:+++***:-.:+=*=#*+=@@@W@@+=*-*W@-:WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW@:.
:-.+*+=**:-:*==@#@WW=@WW#+:WWWWW@=+:-#@-.*WWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWW*..
+...:-:****===*=W@@=@WWW@WWWWWWWWWWWW#WWWW#WW@WW#@WWWWWWWWWWWWWWWWWWWWWWWWWW*..
-:=******+::+:+=---=WWWW@WWWWWWWWWWWWWWW#@###WWWWWWWWWWWWWWW=*@WWWWWWWWWWWWW:..
.+:+:-.-:***+*+:--+*=#=#@WWWWWWWWWWWWWW@*W#=@WWWWWWWWWWWWWWWWWWWWWW@WWWWWW@+...
..+:***=+++**+**#########@@WWWWWWWWWWW@*#@@#W@WWWWWWWWWWWWWWWWWW=W@:+#WWWW@:...
..:++*++::--::***#@@W@@@@@@WWWWWWWWWW@@@W@@WWWWWWWWWWWWWWWWWWW@##@WWWWW@#WWW+..
.......--:+**#####=######=##@WWWWWWWWWWWW@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW=..
.....-::--:-:*=+==#@@@@WWWWWWWWWWWWWWWWW@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+..
....-*+*+:::++:-+**=#@@@WWWWWWWWWWWWWW#W@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW*...
.-:=:***+=#@@@#=*+**+=#@@WWWWWWWWWWWW@#@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW#...
..::*::***+*=#@W@@@@#=#@@@WWWWWWWWWWW@W@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW:..
....--*:*=*+-+=*#@W@@@@@@@@WWWWWWWWWWWW#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+..
......--===+-**+*+###@@@@#@WWWWWWWWWWW@#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@-.
.....---+###*:**-:*:*#**=@@WWWWWWWWWWW@#WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+.
.......-.-=@#=++**:+*++:*##WWWWWWWWWWW#=WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@-
......-:*-.-+@#=++**::*+=@WWWWWWWWWW@:+@W@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW:
......*==@#+--=W#*-:***=#WWWWWWWWWWW=#@@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW+
......-*=WWW@=-.:::+-+#WWWWWWWWWWWWW@#*==#@@@@WWWWWWWWWWWWWWWWWWWWWWWWWWW@@@@W:
......-++#WWWWW@---.-+=WWWWWWWWWWWWWWW@@+*=##@@WWWWWWWWWWWWWWWWWWWWWWWWWWW@@@W+
.......-:=WWWWWWW#--+#@WWWWWWWWWWWWWWW@+--:+=#=#@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-*@@WWWWWWWW=*=@WWWWWWWWWWWWWWWW@+++*###@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-:=@WWWWWWWWWWW=#WWWWWWWWWWWWW*WW=+++*=@@@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW=
.......-+*=#@WWWWWWWWWWWWWWWWWWWWWWWW:@W@===*+**@@WWWWWWWWWWWWWWWWWWWWWWWWWWWW@
.......-*@@@WWWWWWWWWWWWWWWWWWWWWWWW#.+W@+*==#@@@@@W@WWWWWWWWWWWWWWWWWWWWWWWWWW
......-:::@WWWWWWWWWWWWWWWWWWWWWWWWW*.:W@*=#*++*==#@@WWWWWWWWWWWWWWWWWWWWWWW@@@
.......-:*WWWWWWWWWWWWWWWWWWWWWWWWWW=.-@#+*++*#@****=@WWWWWWWWWWWWWWWWWWWWWWWWW
......-:=@WWWWWWWWWWWWWWWWWWWWWWWWWW:..#@:+:+**=**#@@#@@WWWWWWWWWWWWWWWWWWWWWWW
.....-:+=#WWWWWWWWWWWWWWWWWWWWWWWWW@...*@*+*++=#===####@@@@WWWWWWWWWWWWWWWWWWWW
......:+#@WWWWWWWWWWWWWWWWWWWWWWWWW+...:W*+:+*=**#@@@#=###@@@WWWWWWWWWWWWWWWWWW
.....-:+=@WWWWWWWWWWWWWWWWWWWWWWWW#....-@=**+=***=#**#WWWW@@W@WWWWWWWWWWWWWWWWW
......:*=@WWWWWWWWWWWWWWWWWWWWWWWW+.....#===+++*=+*#@@@#@@@WWWWWWWWWWWWWWWWWWWW
...--::*#WWWWWWWWWWWWWWWWWWWWWWWW@......**=##+**+**=*###@@@WWWWWWWWWWWWWWWWWWWW
...--+*=@WWWWWWWWWWWWWWWWWWWWWWWW+......-@===++*+***==*@@@@WWWWWWWWWWWWWWW@@@W@
...--+*#@WWWWWWWWWWWWWWWWWWWWWWW@-.......#=##==#======@#@@@@WWWWWWWWWWWWWW@@@@@',
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_validates_snake_case_when_loading_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'case_key' => 'snake',
            'album_as_album_with_cover' => '{"rlt":{"albumCover":{}},"fld":["name"],"flt":{"f":"id","d":2}}',
        ]);

        $expected = [
            'errors' => [
                'album_as_album_with_cover: Relation "albumCover" is not available, did you mean "album_cover"? (Location: relations.albumCover)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_handles_snake_case_in_relations()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'case_key' => 'snake',
            'jory' => '{"fld":["year_start","id","year_end"],"rlt":{"albums":{"fld":["release_date","band_id"],"srt":["-release_date"],"flt":{"or":[{"f":"release_date","d":"1969-01-12"},{"f":"release_date","d":"1970-10-05"}]}}}}',
        ]);

        $expected = [
            'data' => [
                'id' => 2,
                'year_start' => 1968,
                'year_end' => 1980,
                'albums' => [
                    [
                        'band_id' => 2,
                        'release_date' => '1970-10-05',
                    ],
                    [
                        'band_id' => 2,
                        'release_date' => '1969-01-12',
                    ],
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_validates_snake_case_in_relations()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'case_key' => 'snake',
            'jory' => '{"fld":["year_start","id","year_end"],"rlt":{"albums":{"fld":["release_ate","band_id"],"srt":["-release_dates"],"flt":{"or":[{"f":"release_ate","d":"1969-01-12"},{"f":"releaseDaate","d":"1970-10-05"}]}}}}',
        ]);

        $expected = [
            'errors' => [
                'Field "release_ate" is not available, did you mean "release_date"? (Location: albums.fields.release_ate)',
                'Field "release_ate" is not available for filtering, did you mean "release_date"? (Location: albums.filter(or).0(release_ate))',
                'Field "releaseDaate" is not available for filtering, did you mean "release_date"? (Location: albums.filter(or).1(releaseDaate))',
                'Field "release_dates" is not available for sorting, did you mean "release_date"? (Location: albums.sorts.release_dates)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_load_a_relation_with_an_alias_in_snake_case()
    {
        $response = $this->json('GET', 'jory/band/3', [
            'jory' => '{"fld":["name"],"rlt":{"albums_as_album_no_eight":{"flt":{"f":"id","d":8}},"albums_as_album_no_nine":{"flt":{"f":"id","d":9}}}}',
            'case_key' => 'snake',
        ]);

        $expected = [
            'data' => [
                'name' => 'Beatles',
                'album_no_eight' => [
                    [
                        'id' => 8,
                        'band_id' => 3,
                        'name' => 'Abbey road',
                        'release_date' => '1969-09-26',
                    ],
                ],
                'album_no_nine' => [
                    [
                        'id' => 9,
                        'band_id' => 3,
                        'name' => 'Let it be',
                        'release_date' => '1970-05-08',
                    ],
                ],
            ],
        ];

        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(3);
    }
}
