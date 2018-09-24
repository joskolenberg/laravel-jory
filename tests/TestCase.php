<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        $this->seedDatabase();
    }

    protected function setUpDatabase(Application $app)
    {
        DB::connection()->setQueryGrammar(new MySqlGrammar());

        $app['db']->connection()->getSchemaBuilder()->create('people', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
        });

        $app['db']->connection()->getSchemaBuilder()->create('bands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('year_start');
            $table->integer('year_end')->nullable();
        });

        $app['db']->connection()->getSchemaBuilder()->create('band_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('person_id');
            $table->foreign('person_id')
                ->references('id')->on('people')
                ->onDelete('restrict');
            $table->unsignedInteger('band_id');
            $table->foreign('band_id')
                ->references('id')->on('bands')
                ->onDelete('restrict');
        });

        $app['db']->connection()->getSchemaBuilder()->create('instruments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $app['db']->connection()->getSchemaBuilder()->create('band_member_instrument', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('band_member_id');
            $table->foreign('band_member_id')
                ->references('id')->on('band_members')
                ->onDelete('restrict');
            $table->unsignedInteger('instrument_id');
            $table->foreign('instrument_id')
                ->references('id')->on('instruments')
                ->onDelete('restrict');
        });

        $app['db']->connection()->getSchemaBuilder()->create('albums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('band_id');
            $table->foreign('band_id')
                ->references('id')->on('bands')
                ->onDelete('restrict');
            $table->date('release_date');
        });

        $app['db']->connection()->getSchemaBuilder()->create('songs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('album_id');
            $table->foreign('album_id')
                ->references('id')->on('album')
                ->onDelete('restrict');
        });
    }

    private function seedDatabase()
    {
        // Seed Persons
        foreach ([
                     1 => ['first_name' => 'Mick', 'last_name' => 'Jagger', 'date_of_birth' => '1943-07-26'],
                     2 => ['first_name' => 'Keith', 'last_name' => 'Richards', 'date_of_birth' => '1943-12-18'],
                     3 => ['first_name' => 'Ronnie', 'last_name' => 'Wood', 'date_of_birth' => '1947-06-01'],
                     4 => ['first_name' => 'Charlie', 'last_name' => 'Watts', 'date_of_birth' => '1941-06-02'],
                     5 => ['first_name' => 'Robert', 'last_name' => 'Plant', 'date_of_birth' => '1948-08-20'],
                     6 => ['first_name' => 'Jimmy', 'last_name' => 'Page', 'date_of_birth' => '1944-01-09'],
                     7 => ['first_name' => 'John Paul', 'last_name' => 'Jones', 'date_of_birth' => '1946-01-03'],
                     8 => ['first_name' => 'John', 'last_name' => 'Bonham', 'date_of_birth' => '1948-05-31'],
                     9 => ['first_name' => 'John', 'last_name' => 'Lennon', 'date_of_birth' => '1940-10-09'],
                     10 => ['first_name' => 'Paul', 'last_name' => 'McCartney', 'date_of_birth' => '1942-06-18'],
                     11 => ['first_name' => 'George', 'last_name' => 'Harrison', 'date_of_birth' => '1943-02-24'],
                     12 => ['first_name' => 'Ringo', 'last_name' => 'Starr', 'date_of_birth' => '1940-07-07'],
                     13 => ['first_name' => 'Jimi', 'last_name' => 'Hendrix', 'date_of_birth' => '1942-11-27'],
                     14 => ['first_name' => 'Noel', 'last_name' => 'Redding', 'date_of_birth' => '1945-12-25'],
                     15 => ['first_name' => 'Mitch', 'last_name' => 'Mitchell', 'date_of_birth' => '1946-07-09'],
                 ] as $data) {
            Person::create($data);
        }

        // Seed Bands
        foreach ([
                     1 => ['name' => 'Rolling Stones', 'year_start' => 1962, 'year_end' => null],
                     2 => ['name' => 'Led Zeppelin', 'year_start' => 1968, 'year_end' => 1980],
                     3 => ['name' => 'Beatles', 'year_start' => 1960, 'year_end' => 1970],
                     4 => ['name' => 'Jimi Hendrix Experience', 'year_start' => 1966, 'year_end' => 1970],
                 ] as $band) {
            Band::create($band);
        }

        // Associate persons with bands
        foreach ([
                     1 => [1, 2, 3, 4],
                     2 => [5, 6, 7, 8],
                     3 => [9, 10, 11, 12],
                     4 => [13, 14, 15],
                 ] as $bandId => $personIds) {
            foreach ($personIds as $personId) {
                DB::table('band_members')->insert([
                    'band_id'   => $bandId,
                    'person_id' => $personId,
                ]);
            }
        }

        // Seed Instruments
        foreach ([
                     1 => 'Vocals',
                     2 => 'Guitar',
                     3 => 'Bassguitar',
                     4 => 'Drums',
                     5 => 'Piano',
                 ] as $name) {
            Instrument::create([
                'name' => $name,
            ]);
        }

        // Associate persons with bands
        foreach ([
                     1 => [1],
                     2 => [1, 2],
                     3 => [2],
                     4 => [4],
                     5 => [1],
                     6 => [2],
                     7 => [3],
                     8 => [4],
                     9 => [1, 2],
                     10 => [1, 2, 3, 4, 5],
                     11 => [1, 2],
                     12 => [1, 4],
                     13 => [1, 2],
                     14 => [3],
                     15 => [4],
                 ] as $bandMemberId => $instrumentIds) {
            foreach ($instrumentIds as $instrumentId) {
                DB::table('band_member_instrument')->insert([
                    'band_member_id' => $bandMemberId,
                    'instrument_id'  => $instrumentId,
                ]);
            }
        }

        // Seed Albums
        foreach ([
                     1 => ['band_id' => 1, 'name' => 'Let it bleed', 'release_date' => '1969-12-05'],
                     2 => ['band_id' => 1, 'name' => 'Sticky Fingers', 'release_date' => '1971-04-23'],
                     3 => ['band_id' => 1, 'name' => 'Exile on main st.', 'release_date' => '1972-05-12'],
                     4 => ['band_id' => 2, 'name' => 'Led Zeppelin', 'release_date' => '1969-01-12'],
                     5 => ['band_id' => 2, 'name' => 'Led Zeppelin II', 'release_date' => '1969-10-22'],
                     6 => ['band_id' => 2, 'name' => 'Led Zeppelin III', 'release_date' => '1970-10-05'],
                     7 => ['band_id' => 3, 'name' => 'Sgt. Peppers lonely hearts club band', 'release_date' => '1967-06-01'],
                     8 => ['band_id' => 3, 'name' => 'Abbey road', 'release_date' => '1969-09-26'],
                     9 => ['band_id' => 3, 'name' => 'Let it be', 'release_date' => '1970-05-08'],
                     10 => ['band_id' => 4, 'name' => 'Are you experienced', 'release_date' => '1967-05-12'],
                     11 => ['band_id' => 4, 'name' => 'Axis: Bold as love', 'release_date' => '1967-12-01'],
                     12 => ['band_id' => 4, 'name' => 'Electric ladyland', 'release_date' => '1968-10-16'],
                 ] as $data) {
            Album::create($data);
        }

        // Seed Songs
        foreach ([
                     1 => ['album_id' => 1, 'name' => 'Gimme Shelter'],
                     2 => ['album_id' => 1, 'name' => 'Love In Vain (Robert Johnson)'],
                     3 => ['album_id' => 1, 'name' => 'Country Honk'],
                     4 => ['album_id' => 1, 'name' => 'Live With Me'],
                     5 => ['album_id' => 1, 'name' => 'Let It Bleed'],
                     6 => ['album_id' => 1, 'name' => 'Midnight Rambler'],
                     7 => ['album_id' => 1, 'name' => 'You Got The Silver'],
                     8 => ['album_id' => 1, 'name' => 'Monkey Man'],
                     9 => ['album_id' => 1, 'name' => 'You Can\'t Always Get What You Want'],
                     10 => ['album_id' => 2, 'name' => 'Brown Sugar'],
                     11 => ['album_id' => 2, 'name' => 'Sway'],
                     12 => ['album_id' => 2, 'name' => 'Wild Horses'],
                     13 => ['album_id' => 2, 'name' => 'Can\'t You Hear Me Knocking'],
                     14 => ['album_id' => 2, 'name' => 'You Gotta Move'],
                     15 => ['album_id' => 2, 'name' => 'Bitch'],
                     16 => ['album_id' => 2, 'name' => 'I Got The Blues'],
                     17 => ['album_id' => 2, 'name' => 'Sister Morphine'],
                     18 => ['album_id' => 2, 'name' => 'Dead Flowers'],
                     19 => ['album_id' => 2, 'name' => 'Moonlight Mile'],
                     20 => ['album_id' => 3, 'name' => 'Rocks Off'],
                     21 => ['album_id' => 3, 'name' => 'Rip This Joint'],
                     22 => ['album_id' => 3, 'name' => 'Shake Your Hips'],
                     23 => ['album_id' => 3, 'name' => 'Casino Boogie'],
                     24 => ['album_id' => 3, 'name' => 'Tumbling Dice'],
                     25 => ['album_id' => 3, 'name' => 'Sweet Virginia'],
                     26 => ['album_id' => 3, 'name' => 'Torn and Frayed'],
                     27 => ['album_id' => 3, 'name' => 'Sweet Black Angel'],
                     28 => ['album_id' => 3, 'name' => 'Loving Cup'],
                     29 => ['album_id' => 3, 'name' => 'Happy'],
                     30 => ['album_id' => 3, 'name' => 'Turd on the Run'],
                     31 => ['album_id' => 3, 'name' => 'Ventilator Blues'],
                     32 => ['album_id' => 3, 'name' => 'I Just Want to See His Face'],
                     33 => ['album_id' => 3, 'name' => 'Let It Loose'],
                     34 => ['album_id' => 3, 'name' => 'All Down the Line'],
                     35 => ['album_id' => 3, 'name' => 'Stop Breaking Down'],
                     36 => ['album_id' => 3, 'name' => 'Shine a Light'],
                     37 => ['album_id' => 3, 'name' => 'Soul Survivor'],
                     38 => ['album_id' => 4, 'name' => 'Good Times Bad Times'],
                     39 => ['album_id' => 4, 'name' => 'Babe I\'m Gonna Leave You'],
                     40 => ['album_id' => 4, 'name' => 'You Shook Me'],
                     41 => ['album_id' => 4, 'name' => 'Dazed and Confused'],
                     42 => ['album_id' => 4, 'name' => 'Your Time Is Gonna Come'],
                     43 => ['album_id' => 4, 'name' => 'Black Mountain Side'],
                     44 => ['album_id' => 4, 'name' => 'Communication Breakdown'],
                     45 => ['album_id' => 4, 'name' => 'I Can\'t Quit You Baby'],
                     46 => ['album_id' => 4, 'name' => 'How Many More Times'],
                     47 => ['album_id' => 5, 'name' => 'Whole Lotta Love'],
                     48 => ['album_id' => 5, 'name' => 'What Is and What Should Never Be'],
                     49 => ['album_id' => 5, 'name' => 'The Lemon Song'],
                     50 => ['album_id' => 5, 'name' => 'Thank You'],
                     51 => ['album_id' => 5, 'name' => 'Heartbreaker'],
                     52 => ['album_id' => 5, 'name' => 'Living Loving Maid (She\'s Just A Woman)'],
                     53 => ['album_id' => 5, 'name' => 'Ramble On'],
                     54 => ['album_id' => 5, 'name' => 'Moby Dick'],
                     55 => ['album_id' => 5, 'name' => 'Bring It On Home'],
                     56 => ['album_id' => 6, 'name' => 'Immigrant Song'],
                     57 => ['album_id' => 6, 'name' => 'Friends'],
                     58 => ['album_id' => 6, 'name' => 'Celebration Day'],
                     59 => ['album_id' => 6, 'name' => 'Since I\'ve Been Loving You'],
                     60 => ['album_id' => 6, 'name' => 'Out on the Tiles'],
                     61 => ['album_id' => 6, 'name' => 'Gallows Pole'],
                     62 => ['album_id' => 6, 'name' => 'Tangerine'],
                     63 => ['album_id' => 6, 'name' => 'That\'s the Way'],
                     64 => ['album_id' => 6, 'name' => 'Bron-Y-Aur Stomp'],
                     65 => ['album_id' => 6, 'name' => 'Hats Off to (Roy) Harper'],
                     66 => ['album_id' => 7, 'name' => 'Sgt. Pepper\'s Lonely Hearts Club Band'],
                     67 => ['album_id' => 7, 'name' => 'With a Little Help from My Friends'],
                     68 => ['album_id' => 7, 'name' => 'Lucy in the Sky with Diamonds'],
                     69 => ['album_id' => 7, 'name' => 'Getting Better'],
                     70 => ['album_id' => 7, 'name' => 'Fixing a Hole'],
                     71 => ['album_id' => 7, 'name' => 'She\'s Leaving Home'],
                     72 => ['album_id' => 7, 'name' => 'Being for the Benefit of Mr. Kite!'],
                     73 => ['album_id' => 7, 'name' => 'Within You Without You (Harrison)'],
                     74 => ['album_id' => 7, 'name' => 'When I\'m Sixty-Four'],
                     75 => ['album_id' => 7, 'name' => 'Lovely Rita'],
                     76 => ['album_id' => 7, 'name' => 'Good Morning Good Morning'],
                     77 => ['album_id' => 7, 'name' => 'Sgt. Pepper\'s Lonely Hearts Club Band (Reprise)'],
                     78 => ['album_id' => 7, 'name' => 'A Day in the Life'],
                     79 => ['album_id' => 8, 'name' => 'Come Together'],
                     80 => ['album_id' => 8, 'name' => 'Something'],
                     81 => ['album_id' => 8, 'name' => 'Maxwell\'s Silver Hammer'],
                     82 => ['album_id' => 8, 'name' => 'Oh! Darling'],
                     83 => ['album_id' => 8, 'name' => 'Octopus\'s Garden'],
                     84 => ['album_id' => 8, 'name' => 'I Want You (She\'s So Heavy)'],
                     85 => ['album_id' => 8, 'name' => 'Here Comes the Sun'],
                     86 => ['album_id' => 8, 'name' => 'Because'],
                     87 => ['album_id' => 8, 'name' => 'You Never Give Me Your Money'],
                     88 => ['album_id' => 8, 'name' => 'Sun King'],
                     89 => ['album_id' => 8, 'name' => 'Mean Mr. Mustard'],
                     90 => ['album_id' => 8, 'name' => 'Polythene Pam'],
                     91 => ['album_id' => 8, 'name' => 'She Came in Through the Bathroom Window'],
                     92 => ['album_id' => 8, 'name' => 'Golden Slumbers'],
                     93 => ['album_id' => 8, 'name' => 'Carry That Weight'],
                     94 => ['album_id' => 8, 'name' => 'The End'],
                     95 => ['album_id' => 8, 'name' => 'Her Majesty'],
                     96 => ['album_id' => 9, 'name' => 'Two of Us'],
                     97 => ['album_id' => 9, 'name' => 'Dig a Pony'],
                     98 => ['album_id' => 9, 'name' => 'Across the Universe'],
                     99 => ['album_id' => 9, 'name' => 'I Me Mine" (Harrison'],
                     100 => ['album_id' => 9, 'name' => 'Dig It'],
                     101 => ['album_id' => 9, 'name' => 'Let It Be'],
                     102 => ['album_id' => 9, 'name' => 'Maggie Mae'],
                     103 => ['album_id' => 9, 'name' => 'I\'ve Got a Feeling'],
                     104 => ['album_id' => 9, 'name' => 'One After 909'],
                     105 => ['album_id' => 9, 'name' => 'The Long and Winding Road'],
                     106 => ['album_id' => 9, 'name' => 'For You Blue'],
                     107 => ['album_id' => 9, 'name' => 'Get Back'],
                     108 => ['album_id' => 10, 'name' => 'Foxy Lady'],
                     109 => ['album_id' => 10, 'name' => 'Manic Depression'],
                     110 => ['album_id' => 10, 'name' => 'Red House'],
                     111 => ['album_id' => 10, 'name' => 'Can You See Me'],
                     112 => ['album_id' => 10, 'name' => 'Love or Confusion'],
                     113 => ['album_id' => 10, 'name' => 'I Don\'t Live Today'],
                     114 => ['album_id' => 10, 'name' => 'May This Be Love'],
                     115 => ['album_id' => 10, 'name' => 'Fire'],
                     116 => ['album_id' => 10, 'name' => 'Third Stone from the Sun'],
                     117 => ['album_id' => 10, 'name' => 'Remember'],
                     118 => ['album_id' => 10, 'name' => 'Are You Experienced?'],
                     119 => ['album_id' => 11, 'name' => 'EXP'],
                     120 => ['album_id' => 11, 'name' => 'Up from the Skies'],
                     121 => ['album_id' => 11, 'name' => 'Spanish Castle Magic'],
                     122 => ['album_id' => 11, 'name' => 'Wait Until Tomorrow'],
                     123 => ['album_id' => 11, 'name' => 'Ain\'t No Telling'],
                     124 => ['album_id' => 11, 'name' => 'Little Wing'],
                     125 => ['album_id' => 11, 'name' => 'If 6 Was 9'],
                     126 => ['album_id' => 11, 'name' => 'You Got Me Floatin\''],
                     127 => ['album_id' => 11, 'name' => 'Castles Made of Sand'],
                     128 => ['album_id' => 11, 'name' => 'She\'s So Fine'],
                     129 => ['album_id' => 11, 'name' => 'One Rainy Wish'],
                     130 => ['album_id' => 11, 'name' => 'Little Miss Lover'],
                     131 => ['album_id' => 11, 'name' => 'Bold as Love'],
                     132 => ['album_id' => 12, 'name' => 'And the Gods Made Love'],
                     133 => ['album_id' => 12, 'name' => 'Have You Ever Been (To Electric Ladyland)'],
                     134 => ['album_id' => 12, 'name' => 'Crosstown Traffic'],
                     135 => ['album_id' => 12, 'name' => 'Voodoo Chile'],
                     137 => ['album_id' => 12, 'name' => 'Little Miss Strange'],
                     136 => ['album_id' => 12, 'name' => 'Long Hot Summer Night'],
                     138 => ['album_id' => 12, 'name' => 'Come On (Part I)'],
                     139 => ['album_id' => 12, 'name' => 'Gypsy Eyes'],
                     140 => ['album_id' => 12, 'name' => 'Burning of the Midnight Lamp'],
                     141 => ['album_id' => 12, 'name' => 'Rainy Day, Dream Away'],
                     142 => ['album_id' => 12, 'name' => '1983... (A Merman I Should Turn to Be)'],
                     143 => ['album_id' => 12, 'name' => 'Moon, Turn the Tides...Gently Gently Away'],
                     144 => ['album_id' => 12, 'name' => 'Still Raining, Still Dreaming'],
                     145 => ['album_id' => 12, 'name' => 'House Burning Down'],
                     146 => ['album_id' => 12, 'name' => 'All Along the Watchtower'],
                     147 => ['album_id' => 12, 'name' => 'Voodoo Child (Slight Return)'],
                 ] as $data) {
            Song::create($data);
        }
    }
}
