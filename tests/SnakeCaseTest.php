<?php

namespace JosKolenberg\LaravelJory\Tests;

class SnakeCaseTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('jory.case', 'camel');
        $app['config']->set('jory.request.case-key', 'case_key');
    }

    /** @test */
    public function it_returns_the_defined_fields_in_snake_case()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'case_key' => 'snake',
            'jory' => [
                'fld' => [
                    'year_start',
                    'id',
                    'year_end',
                ],
            ],
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
            'jory' => [
                'fld' => [
                    'yearStart',
                    'id',
                    'year_end',
                ],
            ],
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
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'year_start',
                    'd' => 1968,
                ],
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Led Zeppelin',
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
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'number_of_songs',
                    'o' => '>=',
                    'd' => 15,
                ],
                'srt' => 'id',
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Exile on main st.',
                ],
                [
                    'name' => 'Abbey road',
                ],
                [
                    'name' => 'Electric ladyland',
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
            'jory' => [
                'flt' => [
                    'f' => 'numberOfSongs',
                    'o' => '>=',
                    'd' => 15,
                ],
            ],
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
    public function it_can_apply_basic_sorts_in_snakecase()
    {
        $response = $this->json('GET', 'jory/band', [
            'case_key' => 'snake',
            'jory' => [
                'fld' => 'name',
                'srt' => [
                    'year_end',
                    '-year_start',
                ],
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Rolling Stones',
                ],
                [
                    'name' => 'Jimi Hendrix Experience',
                ],
                [
                    'name' => 'Beatles',
                ],
                [
                    'name' => 'Led Zeppelin',
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
            'jory' => [
                'fld' => 'name',
                'srt' => [
                    'number_of_songs',
                    '-band_id',
                ],
                'lmt' => 5,
                'offset' => 7,
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Axis: Bold as love',
                ],
                [
                    'name' => 'Sgt. Peppers lonely hearts club band',
                ],
                [
                    'name' => 'Electric ladyland',
                ],
                [
                    'name' => 'Abbey road',
                ],
                [
                    'name' => 'Exile on main st.',
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
            'jory' => [
                'srt' => [
                    'numberOfSongs',
                    '-bandId',
                ],
                'lmt' => 5,
                'offset' => 7,
            ],
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
            'jory' => [
                'rlt' => [
                    'album_cover' => [
                        'fld' => 'image',
                    ],
                ],
                'fld' => 'name',
            ],
        ]);

        $expected = [
            'data' => [
                'name' => 'Sticky Fingers',
                'album_cover' => [
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
            'jory' => [
                'rlt' => [
                    'albumCover' => [
                    ],
                ],
                'fld' => 'name',
            ],
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
            'jory' => [
                'fld' => 'name',
                'lmt' => 2,
            ],
        ]);

        $expected = [
            'data' => [
                [
                    'name' => 'Rolling Stones',
                ],
                [
                    'name' => 'Led Zeppelin',
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
            'jory' => [
                'flt' => [
                    'f' => 'year_end',
                    'd' => 1970,
                ],
            ],
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
            'jory' => [
                'fld' => 'name',
            ]
        ]);

        $expected = [
            'data' => [
                'name' => 'Led Zeppelin',
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_get_apply_snake_case_when_loading_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'case_key' => 'snake',
            'jory' => [
                'album as album_with_cover' => [
                    'rlt' => [
                        'album_cover' => [
                            'fld' => 'image',
                        ],
                    ],
                    'fld' => 'name',
                    'flt' => [
                        'f' => 'id',
                        'd' => 2,
                    ],
                ],
                'album-cover:2 as sticky_fingers_album_cover' => [
                    'fld' => 'image',
                ],
            ],
        ]);

        $expected = [
            'data' => [
                'album_with_cover' => [
                    [
                        'name' => 'Sticky Fingers',
                        'album_cover' => [
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
            'jory' => [
                'album as album_with_cover' => [
                    'rlt' => [
                        'albumCover' => [
                        ],
                    ],
                    'fld' => 'name',
                    'flt' => [
                        'f' => 'id',
                        'd' => 2,
                    ],
                ],
            ],
        ]);

        $expected = [
            'errors' => [
                'album as album_with_cover: Relation "albumCover" is not available, did you mean "album_cover"? (Location: relations.albumCover)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_handles_snake_case_in_relations()
    {
        $response = $this->json('GET', 'jory/person/10', [
            'jory' => [
                "fld" => ["date_of_birth", "id"],
                "rlt" => [
                    "instruments" => [
                        "fld" => ["name", "type_name"],
                        "srt" => ["type_name", "-name"],
                        "flt" => [
                            "or" => [
                                [
                                    "f" => "type_name",
                                    "d" => "voice",
                                ],
                                [
                                    "f" => "type_name",
                                    "d" => "stringed",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'case_key' => 'snake',
        ]);

        $expected = [
            'data' => [
                'id' => 10,
                'date_of_birth' => '1942/06/18',
                'instruments' => [
                    [
                        'name' => 'Piano/Keys',
                        'type_name' => 'stringed',
                    ],
                    [
                        'name' => 'Guitar',
                        'type_name' => 'stringed',
                    ],
                    [
                        'name' => 'Bassguitar',
                        'type_name' => 'stringed',
                    ],
                    [
                        'name' => 'Vocals',
                        'type_name' => 'voice',
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
            'jory' => [
                'fld' => [
                    'year_start',
                    'id',
                    'year_end',
                ],
                'rlt' => [
                    'albums' => [
                        'fld' => [
                            'release_ate',
                            'band_id',
                        ],
                        'srt' => '-release_dates',
                        'flt' => [
                            'or' => [
                                [
                                    'f' => 'release_ate',
                                    'd' => '1969-01-12',
                                ],
                                [
                                    'f' => 'releaseDaate',
                                    'd' => '1970-10-05',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
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
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'albums as album_no_eight' => [
                        'fld' => 'name',
                        'flt' => [
                            'f' => 'id',
                            'd' => 8,
                        ],
                    ],
                    'albums as album_no_nine' => [
                        'fld' => 'name',
                        'flt' => [
                            'f' => 'id',
                            'd' => 9,
                        ],
                    ],
                ],
            ],
            'case_key' => 'snake',
        ]);

        $expected = [
            'data' => [
                'name' => 'Beatles',
                'album_no_eight' => [
                    [
                        'name' => 'Abbey road',
                    ],
                ],
                'album_no_nine' => [
                    [
                        'name' => 'Let it be',
                    ],
                ],
            ],
        ];

        $response->assertStatus(200)->assertJson($expected)->assertExactJson($expected);

        $this->assertQueryCount(3);
    }
}
