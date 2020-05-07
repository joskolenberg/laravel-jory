<?php

namespace JosKolenberg\LaravelJory\Tests;

class CamelCaseTest extends TestCase
{
    /** @test */
    public function it_returns_the_defined_fields_in_camel_case()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'jory' => [
                'fld' => ["yearStart", "id", "yearEnd"],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'data' => [
                'id' => 2,
                'yearStart' => 1968,
                'yearEnd' => 1980,
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_validates_the_fields_in_camelCase()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'jory' => [
                'fld' => ["yearStart", "id", "year_end"],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'errors' => [
                'Field "year_end" is not available, did you mean "yearEnd"? (Location: fields.year_end)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_apply_standard_filters_in_camelcase()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'yearStart',
                    'd' => 1968,
                ],
            ],
            'case' => 'camel',
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
    public function it_can_apply_custom_filters_in_camelcase()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'numberOfSongs',
                    'o' => '>=',
                    'd' => 15,
                ],
                'srt' => 'id',
            ],
            'case' => 'camel',
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
    public function it_validates_the_filters_in_camelCase()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'flt' => [
                    'f' => 'number_of_songs',
                    'o' => '>=',
                    'd' => 15,
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'errors' => [
                'Field "number_of_songs" is not available for filtering, did you mean "numberOfSongs"? (Location: filter(number_of_songs))',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_apply_basic_sorts_in_camelcase()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => ["id", "name", "yearEnd", "yearStart"],
                'srt' => ["yearEnd", "-yearStart"],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Rolling Stones',
                    'yearStart' => 1962,
                    'yearEnd' => null,
                ],
                [
                    'id' => 4,
                    'name' => 'Jimi Hendrix Experience',
                    'yearStart' => 1966,
                    'yearEnd' => 1970,
                ],
                [
                    'id' => 3,
                    'name' => 'Beatles',
                    'yearStart' => 1960,
                    'yearEnd' => 1970,
                ],
                [
                    'id' => 2,
                    'name' => 'Led Zeppelin',
                    'yearStart' => 1968,
                    'yearEnd' => 1980,
                ],
            ],
        ];

        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_custom_sorts_in_camelcase()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'fld' => 'name',
                'srt' => ["numberOfSongs", "-bandId"],
                'lmt' => 5,
                'offset' => 7,
            ],
            'case' => 'camel',
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
    public function it_validates_the_sorts_in_camelCase()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'srt' => ["number_of_songs", "-band_id"],
                'lmt' => 5,
                'offset' => 7,
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'errors' => [
                'Field "number_of_songs" is not available for sorting, did you mean "numberOfSongs"? (Location: sorts.number_of_songs)',
                'Field "band_id" is not available for sorting, did you mean "bandId"? (Location: sorts.band_id)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_return_relations_in_camelcase()
    {
        $response = $this->json('GET', 'jory/album/2', [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'albumCover' => [
                        'fld' => 'image'
                    ]
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'data' => [
                'name' => 'Sticky Fingers',
                'albumCover' => [
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
    public function it_validates_the_relations_in_camelCase()
    {
        $response = $this->json('GET', 'jory/album', [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'album_cover' => []
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'errors' => [
                'Relation "album_cover" is not available, did you mean "albumCover"? (Location: relations.album_cover)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_return_multiple_records_in_camelCase()
    {
        $response = $this->json('GET', 'jory/band', [
            'jory' => [
                'fld' => 'name',
                'lmt' => 2,
            ],
            'case' => 'camel',
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
    public function it_can_do_a_count_in_camelCase()
    {
        $response = $this->json('GET', 'jory/band/count', [
            'jory' => [
                'flt' => [
                    'f' => 'yearEnd',
                    'd' => 1970,
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'data' => 2,
        ];

        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_get_a_single_record_in_camelCase()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'jory' => [
                'fld' => 'name',
            ],
            'case' => 'camel',
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
    public function it_can_get_apply_camelCase_when_loading_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'album as albumWithCover' => [
                    'fld' => 'name',
                    'flt' => [
                        'f' => 'id',
                        'd' => 2,
                    ],
                    'rlt' => [
                        'albumCover' => [
                            'fld' => 'image',
                        ]
                    ],
                ],
                'album-cover:2 as stickyFingersAlbumCover' => [
                    'fld' => 'image',
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'data' => [
                'albumWithCover' => [
                    [
                        'name' => 'Sticky Fingers',
                        'albumCover' => [
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
                'stickyFingersAlbumCover' => [
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
    public function it_validates_camelCase_when_loading_multiple_resources()
    {
        $response = $this->json('GET', 'jory', [
            'jory' => [
                'album as albumWithCover' => [
                    'fld' => 'name',
                    'flt' => [
                        'f' => 'id',
                        'd' => 2,
                    ],
                    'rlt' => [
                        'album_cover' => []
                    ],
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'errors' => [
                'album as albumWithCover: Relation "album_cover" is not available, did you mean "albumCover"? (Location: relations.album_cover)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_handles_camel_case_in_relations()
    {
        $response = $this->json('GET', 'jory/person/10', [
            'jory' => [
                "fld" => ["dateOfBirth", "id"],
                "rlt" => [
                    "instruments" => [
                        "fld" => ["name", "typeName"],
                        "srt" => ["typeName", "-name"],
                        "flt" => [
                            "or" => [
                                [
                                    "f" => "typeName",
                                    "d" => "voice",
                                ],
                                [
                                    "f" => "typeName",
                                    "d" => "stringed",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'data' => [
                'id' => 10,
                'dateOfBirth' => '1942/06/18',
                'instruments' => [
                    [
                        'name' => 'Piano/Keys',
                        'typeName' => 'stringed',
                    ],
                    [
                        'name' => 'Guitar',
                        'typeName' => 'stringed',
                    ],
                    [
                        'name' => 'Bassguitar',
                        'typeName' => 'stringed',
                    ],
                    [
                        'name' => 'Vocals',
                        'typeName' => 'voice',
                    ],
                ],
            ],
        ];
        $response->assertStatus(200)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_validates_camelCase_in_relations()
    {
        $response = $this->json('GET', 'jory/band/2', [
            'jory' => [
                "fld" => ["yearStart", "id", "yearEnd"],
                "rlt" => [
                    "albums" => [
                        "fld" => ["release_ate", "bandId"],
                        "srt" => ["-releaseDates"],
                        "flt" => [
                            "or" => [
                                [
                                    "f" => "release_ate",
                                    "d" => "1969-01-12",
                                ],
                                [
                                    "f" => "releaseDaate",
                                    "d" => "1970-10-05",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'errors' => [
                'Field "release_ate" is not available, did you mean "releaseDate"? (Location: albums.fields.release_ate)',
                'Field "release_ate" is not available for filtering, did you mean "releaseDate"? (Location: albums.filter(or).0(release_ate))',
                'Field "releaseDaate" is not available for filtering, did you mean "releaseDate"? (Location: albums.filter(or).1(releaseDaate))',
                'Field "releaseDates" is not available for sorting, did you mean "releaseDate"? (Location: albums.sorts.releaseDates)',
            ],
        ];

        $response->assertStatus(422)->assertExactJson($expected)->assertJson($expected);

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_can_load_a_relation_with_an_alias_in_camelCase()
    {
        $response = $this->json('GET', 'jory/band/3', [
            'jory' => [
                "fld" => 'name',
                "rlt" => [
                    "albums as albumNoEight" => [
                        'fld' => 'name',
                        "flt" => [
                            "f" => "id",
                            "d" => 8,
                        ],
                    ],
                    "albums as albumNoNine" => [
                        'fld' => 'name',
                        "flt" => [
                            "f" => "id",
                            "d" => 9,
                        ],
                    ],
                ],
            ],
            'case' => 'camel',
        ]);

        $expected = [
            'data' => [
                'name' => 'Beatles',
                'albumNoEight' => [
                    [
                        'name' => 'Abbey road',
                    ],
                ],
                'albumNoNine' => [
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
