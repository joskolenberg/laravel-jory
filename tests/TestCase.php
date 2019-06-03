<?php

namespace JosKolenberg\LaravelJory\Tests;

use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\JoryServiceProvider;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\BandJoryBuilder;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\PersonJoryBuilder;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithAfterFetchHook;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithBeforeQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\AlbumCoverJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\AlbumJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\BandJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\InstrumentJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\PersonJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\SongJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\SongJoryResourceWithAfterFetchHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\SongJoryResourceWithBeforeQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Groupie;
use JosKolenberg\LaravelJory\Tests\Models\Instrument;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Models\SongWithAfterFetchHook;
use JosKolenberg\LaravelJory\Tests\Models\SongWithCustomJoryResource;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        $this->seedDatabase();
        $this->registerJoryBuilders();

        \DB::enableQueryLog();
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
            $table->foreign('person_id')->references('id')->on('people')->onDelete('restrict');
            $table->unsignedInteger('band_id');
            $table->foreign('band_id')->references('id')->on('bands')->onDelete('restrict');
        });

        $app['db']->connection()->getSchemaBuilder()->create('instruments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $app['db']->connection()->getSchemaBuilder()->create('instrument_person', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('person_id');
            $table->foreign('person_id')->references('id')->on('band_members')->onDelete('restrict');
            $table->unsignedInteger('instrument_id');
            $table->foreign('instrument_id')->references('id')->on('instruments')->onDelete('restrict');
        });

        $app['db']->connection()->getSchemaBuilder()->create('albums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('band_id');
            $table->foreign('band_id')->references('id')->on('bands')->onDelete('restrict');
            $table->date('release_date');
        });

        $app['db']->connection()->getSchemaBuilder()->create('songs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->unsignedInteger('album_id');
            $table->foreign('album_id')->references('id')->on('album')->onDelete('restrict');
        });

        $app['db']->connection()->getSchemaBuilder()->create('album_covers', function (Blueprint $table) {
            $table->increments('id');
            $table->text('image');
            $table->unsignedInteger('album_id');
            $table->foreign('album_id')->references('id')->on('album')->onDelete('restrict');
        });

        $app['db']->connection()->getSchemaBuilder()->create('groupies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('person_id');
            $table->foreign('person_id')->references('id')->on('people')->onDelete('restrict');
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
                    'band_id' => $bandId,
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
                     5 => 'Piano/Keys',
                     6 => 'Flute',
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
                     7 => [3, 5],
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
                DB::table('instrument_person')->insert([
                    'person_id' => $bandMemberId,
                    'instrument_id' => $instrumentId,
                ]);
            }
        }

        // Seed Albums
        foreach ([
                     1 => ['band_id' => 1, 'name' => 'Let it bleed', 'release_date' => '1969-12-05'], // count: 9
                     2 => ['band_id' => 1, 'name' => 'Sticky Fingers', 'release_date' => '1971-04-23'], // 10
                     3 => ['band_id' => 1, 'name' => 'Exile on main st.', 'release_date' => '1972-05-12'], // 18
                     4 => ['band_id' => 2, 'name' => 'Led Zeppelin', 'release_date' => '1969-01-12'], // 9
                     5 => ['band_id' => 2, 'name' => 'Led Zeppelin II', 'release_date' => '1969-10-22'], // 9
                     6 => ['band_id' => 2, 'name' => 'Led Zeppelin III', 'release_date' => '1970-10-05'], // 10
                     7 => [
                         'band_id' => 3,
                         'name' => 'Sgt. Peppers lonely hearts club band',
                         'release_date' => '1967-06-01',
                     ], // 13
                     8 => ['band_id' => 3, 'name' => 'Abbey road', 'release_date' => '1969-09-26'], // 17
                     9 => ['band_id' => 3, 'name' => 'Let it be', 'release_date' => '1970-05-08'], // 12
                     10 => ['band_id' => 4, 'name' => 'Are you experienced', 'release_date' => '1967-05-12'], // 11
                     11 => ['band_id' => 4, 'name' => 'Axis: Bold as love', 'release_date' => '1967-12-01'], // 13
                     12 => ['band_id' => 4, 'name' => 'Electric ladyland', 'release_date' => '1968-10-16'], // 15
                 ] as $data) {
            Album::create($data);
        }

        // Seed Songs
        foreach ([
                     1 => ['album_id' => 1, 'title' => 'Gimme Shelter'],
                     2 => ['album_id' => 1, 'title' => 'Love In Vain (Robert Johnson)'],
                     3 => ['album_id' => 1, 'title' => 'Country Honk'],
                     4 => ['album_id' => 1, 'title' => 'Live With Me'],
                     5 => ['album_id' => 1, 'title' => 'Let It Bleed'],
                     6 => ['album_id' => 1, 'title' => 'Midnight Rambler'],
                     7 => ['album_id' => 1, 'title' => 'You Got The Silver'],
                     8 => ['album_id' => 1, 'title' => 'Monkey Man'],
                     9 => ['album_id' => 1, 'title' => 'You Can\'t Always Get What You Want'],
                     10 => ['album_id' => 2, 'title' => 'Brown Sugar'],
                     11 => ['album_id' => 2, 'title' => 'Sway'],
                     12 => ['album_id' => 2, 'title' => 'Wild Horses'],
                     13 => ['album_id' => 2, 'title' => 'Can\'t You Hear Me Knocking'],
                     14 => ['album_id' => 2, 'title' => 'You Gotta Move'],
                     15 => ['album_id' => 2, 'title' => 'Bitch'],
                     16 => ['album_id' => 2, 'title' => 'I Got The Blues'],
                     17 => ['album_id' => 2, 'title' => 'Sister Morphine'],
                     18 => ['album_id' => 2, 'title' => 'Dead Flowers'],
                     19 => ['album_id' => 2, 'title' => 'Moonlight Mile'],
                     20 => ['album_id' => 3, 'title' => 'Rocks Off'],
                     21 => ['album_id' => 3, 'title' => 'Rip This Joint'],
                     22 => ['album_id' => 3, 'title' => 'Shake Your Hips'],
                     23 => ['album_id' => 3, 'title' => 'Casino Boogie'],
                     24 => ['album_id' => 3, 'title' => 'Tumbling Dice'],
                     25 => ['album_id' => 3, 'title' => 'Sweet Virginia'],
                     26 => ['album_id' => 3, 'title' => 'Torn and Frayed'],
                     27 => ['album_id' => 3, 'title' => 'Sweet Black Angel'],
                     28 => ['album_id' => 3, 'title' => 'Loving Cup'],
                     29 => ['album_id' => 3, 'title' => 'Happy'],
                     30 => ['album_id' => 3, 'title' => 'Turd on the Run'],
                     31 => ['album_id' => 3, 'title' => 'Ventilator Blues'],
                     32 => ['album_id' => 3, 'title' => 'I Just Want to See His Face'],
                     33 => ['album_id' => 3, 'title' => 'Let It Loose'],
                     34 => ['album_id' => 3, 'title' => 'All Down the Line'],
                     35 => ['album_id' => 3, 'title' => 'Stop Breaking Down'],
                     36 => ['album_id' => 3, 'title' => 'Shine a Light'],
                     37 => ['album_id' => 3, 'title' => 'Soul Survivor'],
                     38 => ['album_id' => 4, 'title' => 'Good Times Bad Times'],
                     39 => ['album_id' => 4, 'title' => 'Babe I\'m Gonna Leave You'],
                     40 => ['album_id' => 4, 'title' => 'You Shook Me'],
                     41 => ['album_id' => 4, 'title' => 'Dazed and Confused'],
                     42 => ['album_id' => 4, 'title' => 'Your Time Is Gonna Come'],
                     43 => ['album_id' => 4, 'title' => 'Black Mountain Side'],
                     44 => ['album_id' => 4, 'title' => 'Communication Breakdown'],
                     45 => ['album_id' => 4, 'title' => 'I Can\'t Quit You Baby'],
                     46 => ['album_id' => 4, 'title' => 'How Many More Times'],
                     47 => ['album_id' => 5, 'title' => 'Whole Lotta Love'],
                     48 => ['album_id' => 5, 'title' => 'What Is and What Should Never Be'],
                     49 => ['album_id' => 5, 'title' => 'The Lemon Song'],
                     50 => ['album_id' => 5, 'title' => 'Thank You'],
                     51 => ['album_id' => 5, 'title' => 'Heartbreaker'],
                     52 => ['album_id' => 5, 'title' => 'Living Loving Maid (She\'s Just A Woman)'],
                     53 => ['album_id' => 5, 'title' => 'Ramble On'],
                     54 => ['album_id' => 5, 'title' => 'Moby Dick'],
                     55 => ['album_id' => 5, 'title' => 'Bring It On Home'],
                     56 => ['album_id' => 6, 'title' => 'Immigrant Song'],
                     57 => ['album_id' => 6, 'title' => 'Friends'],
                     58 => ['album_id' => 6, 'title' => 'Celebration Day'],
                     59 => ['album_id' => 6, 'title' => 'Since I\'ve Been Loving You'],
                     60 => ['album_id' => 6, 'title' => 'Out on the Tiles'],
                     61 => ['album_id' => 6, 'title' => 'Gallows Pole'],
                     62 => ['album_id' => 6, 'title' => 'Tangerine'],
                     63 => ['album_id' => 6, 'title' => 'That\'s the Way'],
                     64 => ['album_id' => 6, 'title' => 'Bron-Y-Aur Stomp'],
                     65 => ['album_id' => 6, 'title' => 'Hats Off to (Roy) Harper'],
                     66 => ['album_id' => 7, 'title' => 'Sgt. Pepper\'s Lonely Hearts Club Band'],
                     67 => ['album_id' => 7, 'title' => 'With a Little Help from My Friends'],
                     68 => ['album_id' => 7, 'title' => 'Lucy in the Sky with Diamonds'],
                     69 => ['album_id' => 7, 'title' => 'Getting Better'],
                     70 => ['album_id' => 7, 'title' => 'Fixing a Hole'],
                     71 => ['album_id' => 7, 'title' => 'She\'s Leaving Home'],
                     72 => ['album_id' => 7, 'title' => 'Being for the Benefit of Mr. Kite!'],
                     73 => ['album_id' => 7, 'title' => 'Within You Without You (Harrison)'],
                     74 => ['album_id' => 7, 'title' => 'When I\'m Sixty-Four'],
                     75 => ['album_id' => 7, 'title' => 'Lovely Rita'],
                     76 => ['album_id' => 7, 'title' => 'Good Morning Good Morning'],
                     77 => ['album_id' => 7, 'title' => 'Sgt. Pepper\'s Lonely Hearts Club Band (Reprise)'],
                     78 => ['album_id' => 7, 'title' => 'A Day in the Life'],
                     79 => ['album_id' => 8, 'title' => 'Come Together'],
                     80 => ['album_id' => 8, 'title' => 'Something'],
                     81 => ['album_id' => 8, 'title' => 'Maxwell\'s Silver Hammer'],
                     82 => ['album_id' => 8, 'title' => 'Oh! Darling'],
                     83 => ['album_id' => 8, 'title' => 'Octopus\'s Garden'],
                     84 => ['album_id' => 8, 'title' => 'I Want You (She\'s So Heavy)'],
                     85 => ['album_id' => 8, 'title' => 'Here Comes the Sun'],
                     86 => ['album_id' => 8, 'title' => 'Because'],
                     87 => ['album_id' => 8, 'title' => 'You Never Give Me Your Money'],
                     88 => ['album_id' => 8, 'title' => 'Sun King'],
                     89 => ['album_id' => 8, 'title' => 'Mean Mr. Mustard'],
                     90 => ['album_id' => 8, 'title' => 'Polythene Pam'],
                     91 => ['album_id' => 8, 'title' => 'She Came in Through the Bathroom Window'],
                     92 => ['album_id' => 8, 'title' => 'Golden Slumbers'],
                     93 => ['album_id' => 8, 'title' => 'Carry That Weight'],
                     94 => ['album_id' => 8, 'title' => 'The End'],
                     95 => ['album_id' => 8, 'title' => 'Her Majesty'],
                     96 => ['album_id' => 9, 'title' => 'Two of Us'],
                     97 => ['album_id' => 9, 'title' => 'Dig a Pony'],
                     98 => ['album_id' => 9, 'title' => 'Across the Universe'],
                     99 => ['album_id' => 9, 'title' => 'I Me Mine" (Harrison'],
                     100 => ['album_id' => 9, 'title' => 'Dig It'],
                     101 => ['album_id' => 9, 'title' => 'Let It Be'],
                     102 => ['album_id' => 9, 'title' => 'Maggie Mae'],
                     103 => ['album_id' => 9, 'title' => 'I\'ve Got a Feeling'],
                     104 => ['album_id' => 9, 'title' => 'One After 909'],
                     105 => ['album_id' => 9, 'title' => 'The Long and Winding Road'],
                     106 => ['album_id' => 9, 'title' => 'For You Blue'],
                     107 => ['album_id' => 9, 'title' => 'Get Back'],
                     108 => ['album_id' => 10, 'title' => 'Foxy Lady'],
                     109 => ['album_id' => 10, 'title' => 'Manic Depression'],
                     110 => ['album_id' => 10, 'title' => 'Red House'],
                     111 => ['album_id' => 10, 'title' => 'Can You See Me'],
                     112 => ['album_id' => 10, 'title' => 'Love or Confusion'],
                     113 => ['album_id' => 10, 'title' => 'I Don\'t Live Today'],
                     114 => ['album_id' => 10, 'title' => 'May This Be Love'],
                     115 => ['album_id' => 10, 'title' => 'Fire'],
                     116 => ['album_id' => 10, 'title' => 'Third Stone from the Sun'],
                     117 => ['album_id' => 10, 'title' => 'Remember'],
                     118 => ['album_id' => 10, 'title' => 'Are You Experienced?'],
                     119 => ['album_id' => 11, 'title' => 'EXP'],
                     120 => ['album_id' => 11, 'title' => 'Up from the Skies'],
                     121 => ['album_id' => 11, 'title' => 'Spanish Castle Magic'],
                     122 => ['album_id' => 11, 'title' => 'Wait Until Tomorrow'],
                     123 => ['album_id' => 11, 'title' => 'Ain\'t No Telling'],
                     124 => ['album_id' => 11, 'title' => 'Little Wing'],
                     125 => ['album_id' => 11, 'title' => 'If 6 Was 9'],
                     126 => ['album_id' => 11, 'title' => 'You Got Me Floatin\''],
                     127 => ['album_id' => 11, 'title' => 'Castles Made of Sand'],
                     128 => ['album_id' => 11, 'title' => 'She\'s So Fine'],
                     129 => ['album_id' => 11, 'title' => 'One Rainy Wish'],
                     130 => ['album_id' => 11, 'title' => 'Little Miss Lover'],
                     131 => ['album_id' => 11, 'title' => 'Bold as Love'],
                     132 => ['album_id' => 12, 'title' => 'And the Gods Made Love'],
                     133 => ['album_id' => 12, 'title' => 'Have You Ever Been (To Electric Ladyland)'],
                     134 => ['album_id' => 12, 'title' => 'Crosstown Traffic'],
                     135 => ['album_id' => 12, 'title' => 'Voodoo Chile'],
                     137 => ['album_id' => 12, 'title' => 'Little Miss Strange'],
                     136 => ['album_id' => 12, 'title' => 'Long Hot Summer Night'],
                     138 => ['album_id' => 12, 'title' => 'Come On (Part I)'],
                     139 => ['album_id' => 12, 'title' => 'Gypsy Eyes'],
                     140 => ['album_id' => 12, 'title' => 'Burning of the Midnight Lamp'],
                     141 => ['album_id' => 12, 'title' => 'Rainy Day, Dream Away'],
                     142 => ['album_id' => 12, 'title' => '1983... (A Merman I Should Turn to Be)'],
                     143 => ['album_id' => 12, 'title' => 'Moon, Turn the Tides...Gently Gently Away'],
                     144 => ['album_id' => 12, 'title' => 'Still Raining, Still Dreaming'],
                     145 => ['album_id' => 12, 'title' => 'House Burning Down'],
                     146 => ['album_id' => 12, 'title' => 'All Along the Watchtower'],
                     147 => ['album_id' => 12, 'title' => 'Voodoo Child (Slight Return)'],
                 ] as $data) {
            Song::create($data);
        }

        // Seed AlbumCovers
        foreach ([
                     1 => [
                         'album_id' => 1,
                         'image' => '...........................-..------..--.--........-------------.------........
..-------------..-----------------------------------------------.---...........
..:=*+**:++*=*+:-*+*+:#*==:*:--+:+-+-:::-+++-*:*::+-------------.--............
..-..........................--------..------------.-------------..............
...................-.......*=+*--.:*+--*-.-:*-.**---...----------..............
..................-+:-::--::*+:*+::*--:++:*++::++:-----:+:-:+---...............
..............--*::+::-----==+-:*+-:::-+--::-:=+++::-:-------:**----...........
.............-+=**-----::---+.-=*-:-------:+-.-*-....---:::---:*:----..........
............--:+:=*=--:+++:---:----:----------:--:::----:++::::+*:-:-..........
...........++:--::--+#*=:::**:-:-:+++++:-:--:-:*++:-::**+::*=+--..----.........
...........+*+::---------:=*+:-:*=+=:--:==+:-:+=**-.-:*#*-...-..---:*-.........
............-+**+:---:---.-----:++*::-::+=*-.-::+::........-----:+*:-..........
.............-*+::***+::-:---:------------..--..----:-:---:+*++-:++-...........
.............*##@@++*+:-***++:+*++::::::::-::::::+:::+**=::++*#@@#:............
.............*=##@@@@@*+-:+++++*==+:++***=::+++===:+:+++=#@@@@#@@=-............
.............-*=####@@@@@@@@@@@@@=***:++++::***=#@@@@@@@@@@@@@##=-.............
.............-++=####@@##@####@@@@@@@@@@@@#==#@=#=#@@@@@@@@@@#=+--.............
............-:+:++*=###@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@####===*+:-..............
...........---:::+++*==***=#@@@@@@@@@@@#@@@@@@@@@@@@@=*++*=++:-----............
.....--....---++::-::+++**===#====#############===####*::----:==::--...........
............-:+:+:*#=:----:::+++***=@###*#*++=+:-:----.-+=@@#+::+---...........
..-----------::::+++::+=@@@@#=**++::::::::+++**=##@@@@@=*+++++::----...........
..-------------::::++++++*=*+++*==##@@@@@@@@@@###==###=##==+:----.-............
.-----------------::::******==####@#@###@@#####@###=***+::------...............
.-----------------------::::+:+*=*==*====#*****:-:----------...................
--------------------::--::::::--------------------------------...............-:
------------------:::::::::::::::::+**=*:===**+:-----------*##=+:-...-:*==*++::
-----------------::::::::-:*#@@@@@@@@@@#*@@@@@@@@@@@@#+--*@@@@@@@@*+:::::+:::-:
------------------:::::#@@@@@@@@@@@@@@@##@@@@@@@@@@@@@@@=@@@@@@@@@@=:-:--------
----------------::-:#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@#@*----::+++::
----------------::@@@@@@@@@@@@@@@@@=**==#=====@@@@@@@@@@@@@@@@@@@@=::-----.....
----------------+@@@@@@@@@@@@@@@#=*==*==#=*=====#@@@@@@@@@@@#@@#=--............
-.--------------@@@@@@@@@@@@@@@@=#=#=##==###=====@@@@@@@@@@@@@@@W+.............
-.--------------@@@@@@@@@@@@@@@@@@==+=@=###*=#=@@@@@@@@@@@@@@@@@@:.............
-.--------------:@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*..............
..........--------=@@@@@@@@@@@@@@@@@@@@@@@@=**#@@@@@@@@@@@@@@@=-...............
................----+#@@@@@@@@@@@##@@@@@@@@#*++*@@@@@@@@@@@#+..................
.......................-+=@@@@@@@@@@@@@@@@@@#=***=@@@@#*:-.....................
.............................-::+*===#####==**++::-............................
...............................................................................',
                     ],
                     2 => [
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
                     3 => [
                         'album_id' => 3,
                         'image' => '....-@@+*::+=W+:-#WW#:@=WWW@*#-+@W@@W#---*-*---@@=@#=*+#WW*@*W@#W==WWW##W@@@@@@
---..+@:++:***--::+:::*+:*::**:++*+*=*-++=**++-==++----:+:**=====+++=#*-::::+:-
#=#:--@+-:WW@----*+*=#--=#@WWWWW-#@#:+:***=++-+*+:+-::*-*=@@WW@#@###**W+@W##*##
==@*#@#:#:W#@+--:=+##*=-+++@+-=W.=:=#=+*-#**-:--=@@*=@*=+@@@#=@@@W@=**#*#*###@@
+W@##=#:@=W##-*--:-=#+*---+++#W+:***+#*:----------::----+@@+:-:@=+++=*:*=*#@@=#
+:::WW@+=**::*@:-+:*=+*-----:W@---**----*==*++:----++:::*@W:--:@@:=*:-:*WW@##WW
-.*WW=::=#@--+#--+*@@*:-:-@WWW@-----+---=**===**:*+++#+=*-+@**@@#WW@:+:+W@###WW
-::*@---#***===---::-*::*+##@@*----..---:++*++:+::::+=+*#===####@@#@@=#:+++*+++
*::+#*----:+*=@=WW@#.----------------:=++++=*++*+-:+*=##@#@=+:+-::+=---**:+:::*
W@=:+#*:::+*:--:WWW@..---:==-.-------::::::*::::--+*=::-*:+@-------:*--::-::::*
=-...-**W@=**:+#*=@#...-=@:==+:------:WWWWWW#-----***++:+-:-------:=::-::::::-*
#-...--+@=**#@@++:+@..::-:**:--------:WW@#WWW*....*+*=+=:--*+----:@@#*:-::++++:
@:....++@*--=##+*+=@.-+*--:::--------+WW@WWWW#-..-*+==#::*:-------+=W@---+:**:+
@=.....:W#WW@***#@WW...-:-----:------+W@@WW@-.-..-*:::=W=-*:--.---+WW*--::+:**:
@#*....:W=+@W#@--#WW..::--=-:-*------+@@@#WWWWWW:-*+:+##@W=:+-:+::*WW+-:-+*=+==
@*+....-#@*=@@W+*++@...------:-:-----*WWWWWWWWWW-:=*+*WWWWW@-----+=WW#++*@WW@@@
@#:....-++-++++*==*+:+****+++:::+=WWW@@@#####+**+::-::*###@+--:-::::--::++++++=
--------::-=:+*:+:::-:::-:=+--::=WW@W@*@WWWWW*:++=*=:*++*:*-...*@*...=#@*=@@W:-
=::+-+:-:*+*++*=:*:+-::+++++--::*WW@@#=@+@WWW*---:===-.--:W@+-.=:-...+#+=+@WW+-
#--*-=+:++*##=#=:+++-:::::**--::*W@#+@@@@#@WW*..--=@+..--:WW@#**.--=-+=*#=*@@+-
@--=@@=+++*#=**:+:++-::+**:#=:++*WW#=W@*=+#WW=.--+#=:...-:WW##@@===-+**#=#=#@:-
@*:*@@#=::*@+**=*++*:++*==*#@+:+*WW@@@**@-=W#*---=@W*...-:WWWW@W:#W@-+*:=*:@=:-
*+*+:*++==@@@@@W@*#@+*=*#==*=+++*WWW@W=#@@:W--...=WW#..--:WWWW@W@=+=+*@WW##*++@
=-+@=:=-WWWW@@@W@#@@+=WWWWWWWWWW=@WWW@@@@@@W:*++*=@@@*==#*WWW#@@=..=:+:--:::--:
.......:=*****+++--+@@@@WWWWWWW#*+++:+*:::+:+-:+:++:++++-....-:----....-----==-
.......*WWWW@W:+:--:@#+@WWWWWWW=*++++==#++==+-#==#-=:*=#=---*:=@*--....----:@#-
.......*WWWWWW::+--:@=@@@=#=WW@=*+++**+@+*=@*:=+-=-+++---.-.-=@.---....--:++*@+
...--..*WWWWWW:-:-::#@@WW@@WWWW@#++***====#@#----:--:-+::--.-+=+:*-.---+=#=+*=-
.-+#=-.+WWWWWW::++*+#@W@WWWWWWWW@*++*+*#@@@@=+--.--:+----.---+@-#@W##---:**==+-
*@##W*-*WWWWWW+::*WW=WWWWWWWWWWWW+++++==#*#W*+::::-:*-.:-.--.*@*=*#W#------#:*.
.*WWW*.+WWWWWW+:::WW*WW@@WWWWWW@#+::++*+=***+--.@*+*:.+:----*-=*=:::----:+:-*--
WWWWWWW=WWWWWW+:+-@W+::::*:+:+:::::::+::+++++::+++++**+---=#@#==@*:+@#::::+++++
+#*=+=W*+::::----*#+W@=WW@@WW#-----:::----:##+****+#+*+++*+*+*+W*:+-@@:*+++:-.-
*-+**+*:*+##=+==#*=+#-::*:+*W@------------:=#=##@@*=++*+++**+*:::==##@*+**+-#**
--..--:+=*##@===@W@*#=++@*@#@@:-:::+:==++:-*=*@WWW*#+**+*+=@*+:=::-++==+*:+--..
.......-WWWW++WW@=@*WW@WW@@@@@:#W#=##==*+:-*#+WW@==#++**++*=++*#+*++++++:::+***
***+:+++----.-------:-------:---==#WW#*==*:+#+#@W=*@+#@=###W#@WWW#::::::::::+#W
WWW@@@@*..--=@@@+..----+----*#+*+##@#@#@**::=+*#@=*#*W@@@@@@#=@@@@::-:::::::+=W
#=##@@#:----#---:..-:---:=@@.=:-:===*****===+:*:*+++:+*****=**==*=+:::::-:-::#W
W@@#WWW+.---*++:-.--==---+@:-:--*WW@#=+::*=*-:::===+-::::-@WWWWW@W@@WW#----:+=W',
                     ],
                     4 => [
                         'album_id' => 4,
                         'image' => '********=*++++:------------------...........--:*++-+=*=#**+*===+-.-...........-
*=*=====+==*****=****+:*=++-..................--:+=#*:*=*--*#=++++-....:++:+::-
========*==***+-*-:*+**+*+:..................:++===*+.+******==:=:.....-:--:---
==***==*++:-------.-.---.....................-**:*++*:==*=#=**==+=-...........-
=*=*****+::-------.-...------................--**:.-.:#=::*###=+*#:........-+*=
***+******::::---------.-------.........---..-.-:+:..-+=++=**==#=.........:=@W@
+***=**==*++++**+*=*::::--------..--.......-...-:+**..-++##*###+:+=:...---#@WW@
*****#=**=#@@WWWWW@@@**=***+:::-------.........:+-++*-..-=*###=+@=*-.-..:@@WWW@
*****=*=@WWWWWWWWWWW@@W#:-.-+**+::-:------.-..-******=:.-*@##==#=:.-.-..+@WWWW@
+++**+*@WWWWWWWWWWWWWW@@@@=:--..-:**+:--------...--:==+*:===**=#=-.-...-.--=@@@
+:+++++=@WWWWWWWWWWWWWWWWWW@@@*-.....:+*:-----------:=**==*====+-......+-.--#@W
++++++*:=WWWWWWWWWWWWWWWWWWWWW@#***:....-+*+:------::+**======##*.....--....+@@
::::--:-:*WWWWWWWWWWWWWWWWWWWW@@@W@=:-::....-:*+::+*******==###:::..---..--.=@@
:::------.:@WWWWWWWWWWWWWWWWWWWW@@W@@##=----....:=***=**=*+--:-.--.:+..==-.+@@W
--------...-=@WWWWWWWWWWWWWWWWWWWWWWW@@#++=+---....-+:=+-......-==#=:--*@WWWW@@
:------......-#@WWWWWWWWWWWWWWWWWWWWWWWW@W@=+*=---....--.......-+**:#=:=@@+@WW@
:-----.........-#@WWWWWWWWWWWWWWWWWWWWWWWWWWW@@+:=:.............*-.:+-+@@:.-=@#
-----............-*@WWWWWWWWWWWWWWWWWWWWWWWWWWW@@@*++:-........:@=-:=+#*:..--@#
-:--................-#@WWWWWWWWWWWWWWWWWWWWWWWWWWWWW@===:-:-...:-*:-:@:...=@#@W
-......................=@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@*#*#===@*+-::.:-.=@@@@
-........................=*#@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@:......==@=*#
-...........................-*@WWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@@@=++.....-@@@W@*
................................:#@WWWWWWWWWWWWWWWWWWWWWWWWWW@@++++-....:@WWW@*
...................................-+#@WWWWWWWWWWWWWWWWWWWW@=#@****-...:+@@#++@
.......................................-*@WWWWWWWWWWWWWWWWW@=#@@#@+--..:@@@**++
..........................................-#@@WWWWW@@@=#@@WWWWW@=**##-.-**W+::+
...............................................+@@WWWWWWWWWWW@=#@W@=*#::@WW@@@#
...................................................:=@@WWWWWWWWW#@WWW#=@W@@==#=
.......................................................:=WWWWWWWWWWWWW#WWWWW@@#
..........................................................:@@WWWWWWWWWWWWWWW##@
............................................................:@@@WWWWWWWWWWWW@@@
.............................................................:*:--::+@WWWWWWWWW
...............................................................-----#WWWWWWWWW@
................................................................---=WW@WWWWWWW#
...............................................................--:=@@=@WWWWWWW@
..........................................-...................---*@@==@WWWWW#@@
..............................--..........-:...............----:*W@@@@@@#####@@
.............................-----------.----.............----:*@@*@@#@=@=====@
.....-=+.......-.............-------------.----.........------*W#@*:@#*=*****=#
.....-@#......#@=-.........-----:::-:--:+:------..----------:*@W@##@W@@@@@@@@@@',
                     ],
                     5 => [
                         'album_id' => 5,
                         'image' => '++*++*+++++*++++++++*+++++++++++*+++*++*++*++*++++*+++*++*++*+++*++*++++++++*++
+**+**++++**+*++**+**++*+++***+**++**+*++*++**+++*+++*++**+**++**+**++*+**+**++
===============================================================================
===============================::*========+-*========================*=========
----:+*========================+:*=:-:+===*:+:::-:=======*:+========-:+*=======
-----------+===================++++----:+=*+:-:::+:+*=*:+---:+======-:=========
----------------+==============++:---:--:::+:-+::----**--------:-::+-:=========
--------------------:*=========*+::+*+++++++:+++------:----------:-:::*===*====
------------------------:*=====***+*********+++-::+:::::::+::+:+:+:+::-+::::===
----------------------------:====*::+++::+++*+***++***++++*++*+*+++*+:::+-++===
--------------------------------+=====*:::++++++::+++*+*++****++++++:::++:=====
-----------------------------------:==========*:---+++++:++++::+:-:--::::+:+===
---------------------------------------*========*::++::+::::+:-:--:+:-*=-:::===
------------------------------------------+======++:::::------:-----:+---:-:===
---------------------------------------------::**:--:---::-------------:---+===
----------------------------------------------------:------------:+++:-----:===
----------------------------------------------------------::---:----+++::::+===
-------------------------------------------------------------+---::::-+:--:+===
---------------------------------------------------------::::-::::-:::-----:===
------------------------------------------**++--------::---::-:------:::::++===
----------------------------------------:**++:-----::+:-*+*+--:::::+*+:--::+===
----------------------------:+:-+**+--+*******-:::--+::--:++--:--:+--+*::-:+===
:-------------------------+****-***+--*=****===--********+***+:---+:-**+:::+===
=====*:--------------------::::-:***--+**+===:*=****************:-:+*+****++===
============*+:-----------+****+==+*====**=======*****=*++********++*******+===
========================********==*==================*************-+********===
========================******==========*=========*:===**-+*******:*********===
========================*******===================-:*=***+********+*****+***===
========================***++*******==*============****++****+:***********+*===
========================*************=======*=====***==*++**************+*++===
========================++*******=**===========*====*+*::++-:*************+*===
========================*+*****+****=******====***+*+:*++**:-+********+:++++===
========================******++*********=====***=*:+***+:+*+--+******::****===
========================*******+**********======*=*:-+++:*++:---+******+++++===
========================******++*******===========*::--:------:::*****+*++++===
========================*******-********============****+++++**++*******++++===
========================*++****:+*******=======*=*===*****::***+*++++++*+++:===
========================*++++++:-+:****+:+***+**************:+*+:::--:-----:===
========================+----:+*+++::::---::--::++++--:+*****+---:---------:===
========================+-------:++-----------------::------:::------------:===',
                     ],
                     6 => [
                         'album_id' => 6,
                         'image' => '@###@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@#*@==@@@@#########################@
#-.------:=*=*:-----::-----=#=*++::---:*=*--:+:++**:----------:+::--.....-+*#--
#-..------+::+-----*@-------*##=*++----::+=***:---:++:---------.-::-.....--....
#-.-----------------::::------=##**:-----+#==#+-:::::::+++++**-..........-@@*--
#-:++++:----::------::*+::**+++::*=::+++:-----------:++--..--............--#@#*
#-...---:+-----:*----:=#=#==:-------------+:-------:===#:-...-++-...+W@::+=#@=*
#-....::----.----+-:+:=**#=#+-:**+*+****+:--+---*---+*==-...++++*+--:#@W#*--+--
#....+--::::++---+-+--#*=#===--:::++:--------+:-=#=+-...-.......:#@#=:+:.......
#-.-+---++**+---+--*---*===+--:**----*--::----+-----------....-:-:+-+**........
#--**=+--:+:--.+:---:*+:::+*+:-+----*:---+:::-+--------:-+......---+.++-+-.-+::
#.:--*@:*=*-..+:-.---+---+--:-+*+::*:-:::#::++*:-:::::-::-:-..-=#*+.+-:=+......
#-+..+*---.-:#+--:+:*-..--:*:----++:*+--:*-*---:-----:++=*=+:-.::+-+-+.--.-=+-.
#+-...----*=:-**-:*:-:*=*--------:=:-**-:+*---+***---*-+=*--**.:+-::*..:-.-:--:
#+....-:*--*--*::-*--**-----------+--=+:-+:+:--+*----++:---+++-::-++--+*..-+:.-
=+-.-:-+-+:*--.-*-:+::-:::::::+*--+----*++=-*:----------++-:-:*+-*::..-+:+:.*--
=+-..-:*--.-+*+:+*-::-.-+##=*:-++:**:--:*++-:+*----*-++-------*-+:*+*+::+:.---:
=+-:::--:++::-:...:+.-+*+:--+*----**+:-=*---**----+++:-------*-+--.....-++:...-
=+---+------:@=====--:++=*+-*--------:=++++=+::--+:------------........--::--*@
=-+---..----*##==#=:+++:-+:--++*==**+--+:-----:*----+:+---*@+*##+-.......:****=
=-.::---..---=####*::---:#*#=#:--#@@*-+*++:++#=-:+--:*+:*#=##@#@*=--.......-#*=
=..---+:-...-:=*:++-...-#+=====::***+:=:++-*##*:-+-+##+:#=*==@*:#**+::+*+:::#*=
=:+*::+-.-+++**++:-+...-#=*####:++:+:-:**+--:---*-:*@@+-=#=##*+++#+-++**=***#:=
#**++::--:-.........++---+=#*:-+++++*----++:+*=::+**#::--+@@@@++#:...-+#*+..+@W
#+**:.................-+*+----:+:-----::**+:----+==*=##--...---.......:***=***@
=:-.......-++++-....--:*@+-.---.--------------:+**#**=+-.--:-..........--*:--..
=.........::::::--:#@*==+:--.------:++:---------*==+*=++-#@==+.........-+=::*--
=.........---......:#*#=W@:------*+*********+*==+:=----.-:@W#--::***+#@#-...-..
=......-=***=-...-=+:==-:+++:---*-----*++::++-*#**----.-:***+***++*****:.......
=.........:--.....-+--...-=-:+---+---++:--*+:--*--:#*:-:+++*********++:*:..-..-
=..................-+-.....:+-.-:+**=-+--*-*--+---*=:::---+:+:+*:::-::-...:++--
=....-+..........--:+:--.....---*++*+*--:**:#:*=---:-:++:-::-.---:--+++--++++:+
=--*=+-..............--......::-.-------------:+::+--+**+--::-::+-*:=-...-+++#+
#=##-......:=-.............--+*+*##-----------#*:**+++:-+::++=*#=*:-....--.-:*:
=-.....-***+#@+..:-.......-.--.-##*--:==:+:--+@@@@@=*::::+:-*@@#==:-...==#+...-
=-...-+:-:+++=:++:-.......--.......--+*#=++-.-#@#::+*-......-+##=*===-.---..-..
=-.--++:+:+--::+=:**...-........----:#**++:-....-:-:......--::---...-++...-@+.-
=:-:+:+:+::++:+:#+:--...+*-......-:*==+***+--.....--+-...---:::::...-**+-.:@+--
=:+=+++:--+::*:=+---:...............---:-*=:-.....-:-....--::----...-:+:-++++#=
#+:::+:+++-:-:*#-.-+*:.......-***+:-......-......:+:....--------........:-=@#**
#++::::+*::+*#==.-:::-.......-:::-::::--..........:*:........--.........----=--',
                     ],
                     7 => [
                         'album_id' => 7,
                         'image' => '*+++++++++**+++++++++++***++++++++++++****+*****************+*+:+*+++++++++++++
**++++**+:+***-=*#=+**#@*+**:+::#=:=*******=W@**:+==****=*#=**+++=#++++++++++++
***@=***=*=*+++=@=*+*#*+#**=###*#*+*****+=##@@=+::*@+***===#*==+:=*+=*++++++++=
**@#=****##W@=+@@#+:++#=+**=@@#@=##*+**=#@=*#@=#:++++:+=#@@@#@@@#=#*==*+++++#@@
*===@#*+=*=WW##=:+:*+-*+@WWW#*+*++****-*###@WWW=+=*##=*::*++@##@@=####@@#=*#@@@
*=*+*=++#+#W@#=###*-:=W@=@*=:+=@#=@=:+:=#==#@++#**=++*@@+==*=@#==*@@*###@@@@@@@
**#@@##*@=#WW@@@=+#+*@@=*@=@**#@@#=@==@##*=#####**=@==W@+=**WW@#**@@@@W@#@WWWWW
+*=##=+++=@@=+:+=*#*--**@@=@==@W@@#==#*@*=+##=#W**@=::#*+*#@*::@#@@@@@@W@@WWW@W
*==@##**=@##====#*:++::=+:#@#@@@@#@*:+*WWWW@@#@###=#==##=@=@#::@W@@WWWWW@@WWW@@
@W@###@=#WW###**@@@@#=*=:*#@@@@W@#=###WW=**@@#@@##@@@*#@W#==@+:@WW@#@@@@@@@@@@@
@@@##WWWW@#@##@WWWWWW=@@*=@W@**#@@=*=-:##==@@@#W@##*:=###=*:=#@@W@#**@@@W@@W@W@
@@@W@#==@@#@W@W@@@##@@W##=@#+##@@@=#*+++++*==+###===*##*=***++#=@==*#@@WW@@W@@@
W=+*@+#=W=@#WW#=#@==W@#@#=+**=*=*===@##+:==*+*#=*===*+#====+@=#####***=@@@@WW@@
--+---=@WWW@@WWWWWW@W@@@=+#@@@#=====#=#==***==##*+====##===##@#=#*+*+=*#@@W@W@@
-+--:::@WWWWWWWWWWWWWWW#===@@#@#=####=+=#@=*#=@=:+=#==#*++=@@###@@=##@##=#@W@@@
+:#@:-:@WWWWWWWWWWWWWW@**#@###@**###=@#@##**===#:==#==#+*+=@@W#*@@#*@W@#=#@WW@@
=W@+-+#WWWWWWWWWWWWWWWW=+#@#=###=**====#=#==+@##*===+*@**+#@@@@@*#=:=+#WW@W@@@#
*:-:+#WWWWWWWWWWWWWWWWWW++===*#===###@==#==@=@W#===**#@=+*#@#=@@@@#=#==WWWW#==@
##-*@WWWWWWWWWWWWWWWWWWW@@##*+=*====#@##@@#==#W#======@**==@#=@@@@##==@WWW@***#
#@@#=W@@WWWWWWWWWWWWWW@WW=++++#+#=**#=====#=##@####@#=##==#@@@@@W@@@==@WWWW@###
##==*@WWWWWWWWWWWWW@WWWW@@+===@##==*+*++*=#=#@@#=@########@@@@WWW@@@=#WWWW@@@@=
@#==+@WWWWW@@WWWWWWWWWWW@@####@#**+*++:*+#+*##@@=@@####@@@@@W@@W@@@@#@WWWW@=*##
@=#**@WWWWWWWWWWWWWWWWWW@@@@@@=+*-:++==+*+==:=@@@@@##@@@@@@=@#@WWWW@@@@W#=@#*##
@====@WWWWWWWWWWWWWWWW@@W@@@W#+*+:***#===#+=#:#@@W@#=@W@@@@@@@@WWW@@@#@*:+##*##
@==**@WWWWWWWWWWWWWWWWWW@WW#W+::-+:::::+::++*++WWW@##@W@@WWWWW@@@@@@@@@**#*--+=
@#***@WWWWWWWWWWWWWWWWWWWW@@W*++:+:+:-:++++--:+WWW#@+..=WWW@@@@W@@@@@@@*#@+*#*#
@#**=@WWWW=#@WWWWWWWWWWWWW#@@#::++==*====#*==:=W@W@@#+*=@WW@W@@@W@@@@W=:@@+@@@W
@=*=*@WWW@**@WWWWWWWWWWWWW#@=@**:+:*==#+=***:*WWWW@#==+=WWWWW@###@WW@#@@W=+W@WW
WWWWWWWW@#@@@WWWWWWWWWWWWWWWWWW=+:***+:=*=+:=WWW@W@@@===WWWWW###*=WW@@WWW@@WWWW
WWWWWWW@@@@##@@WW@@WWWWWWW@WWWWW@@+:+=+++*@@@@@@W=:*+@@:--:@@@@#=#WWW@WWWW@=@##
@WW@@@@@@@#*=@@@@@WWWW@@W@@@WW@@@@#@@@#@@@######=#@***=++*=:@@#=*=@@@@==::+@###
##=**@#@@@####@@W@@@@@@@W@@@@@##@@@@@@@#@@@#@@@##@@@@@@#@@@@##@@@#@@@@@@++##=*=
*###@#@@@W@######@W######@W@=#@###==##=##@##WWWW@WWW@===####@@=#=####@@W@*@@@@W
@@@@@@WWW##@W@W######@@@@@W##@W##@W@##@WWW@=#@@@@@@@#==#@@@@W@@####@WW@W@@@WWWW
@WW@#WWW@##@####@@#@@###@W@=#@####W@#=#WWW@##WW@@WWW@=#W@WW@WW@@W@####WW@###@@@
W##@@WW@####@@##@@#@WWWWWW@#@W@@##@==#@@@@W#####=#@@@#=######@@##W@#@#@@#@@W@@@
@@@@#@WW@@@@@@@@@@#@##@#@W@@WWWW@@W###@@W=**=@@@@W@WW@@@@@WWWWW@@@@@@#@@@@@@@@@
@WW@#WWWWWWWWWWWWWWWWWWWWWWWWWWWWW=@@#@W#++=+*++:*::*=+***=+*:::WWWWWW@@@@@@@WW
W@@@WWWWWWWWWWWW@#@@@WWWWWWWWWWWWW@@@#@W@*#W@#*=@@=+==:+:=#@@#+:#@WWW@WWWWWWWWW
WWW@@WWWWWW*###=#*:*#@#@=*@W@WWWWWW@@@W@WWWWWWWWWW@**+**+++*++*:@WWW@@W@WWWWWWW',
                     ],
                     8 => [
                         'album_id' => 8,
                         'image' => '@@@@@@@@@#@@@@@@@@@@@@@@@#@@@@=#=:----------------------------------------+=###
@@@@@@@@@@@@#@@@##@@@@@@@@@@@#=**+:---------------------------------------+###@
@@@@@@@@@@@#@@@@#####@@@@@######+++------------------------------------++*#@@##
@@@@@@@@@@@@@@@#@##@@#===###=*=:*=:---------------------------------:::#@@@##@@
@@@@@@@@@@@@@@######==###@@=*#====*+++**:------------------------+::=*@@#@@#@=#
@@@@@@@@@@@@@@@@@@###@@#@@@####=###===*----------------::--::-::+=:*=+*#@#@@@==
@@@@@@@@@@@@@@####======#@#=#==****+::-------------:+**====#=*=*#=**=*=#==##=#@
@@@@@@@@@@@@@@@@#=#@@@######=#=##+----------------:*=######=#==#==#=@###===#=##
@#@@@@@@@@@@@@@@@@@@@@#@@@@@@@@#=+---------------+=##=##=###=====#@@@@@#@##@@@@
@@@@@@@@@@@@@@@@@@#@@@@#====#@#=##*-------------+=#@#####========@@@@###@@@@@@@
@@@@@@@@@@@@@@@@@@@@@@@@@@####@#=+:::-----------*#=#=@#@##=###=#@@@@@@@@@@@@@@@
@@@@@@@@@@@@#@@@@@@@@@@#@@@##=#===*:----------:-++===#######==#@@@@@@@@@@@@@@@@
@@@@@@@@@@@@@@@@@@@@@@@@#@@@###=#==*:---------:=#@@#@*=#####@@#@@@#@@@@@@@@@@@@
@@@@@@@@@@@@@@@@@@@@@@@@@@##@@####+:------:+*==#@####=#=#@@@@@@@@@@@@@@@@@@@@@@
@@@@@@@@@@@@@@@@@@@@@@@@@####@###=+------+#=#=#@=#@#@*=##@###@@@@@@@@@@@@@@@@@@
@@@@@@@@@@@@#@@@@@@@@@@##=##@#=#@#=+----+###==#@@@@@@=@@@##@@@@@@@@@@@@@@@@@@@@
@@@@#*#@@@@@#@@@@@@@@@@@@=#@@###@===*-==###=@##==@@@##@@@@@@###@@##@@@##@@@@@@@
@#@==*#@@@@@@##@@@@@#*=@@#==*@@@##=#==#=######=+++====#@@@@@@@@@@####@#@@@@@@@@
@@@@@@@@@@@@@#@@@#@@@==#@#*+=##*==+++++:***=*****=====#=#@@@@==@**===@=*#=@@###
@@@@@@@@@@@@#@@@@#@####====+***::+**********=*=+****::*@@@@@#@@@#+***#@=##@@@@@
@@@@@@@@@@@##@#===*:-:*###==**=@##***==***==***==###=+*@#@@@@##@@#@@@@@@@#@@@@#
@@@@@@@@@@@@++*::+++:=*+####@@#**========*=======#@@#+*#@=#=#=###***@@@@@@@@@@@
@@@@@@@@@@####**=:-:-:*-####@=:*=##====*=*===#=*:*****=====***=##==*####@@@@@@@
@@@######**=#==#*+**+##@#=====#*=========**==@#:===****++**++**----***++******=
########=**=*=@@@@@@@#=====####=#=======***=@@@@=======******+:----**+*****+***
########=*+*=######======*=#####@#=======*:=@@@@#========****+-----+++=#*+**+++
@########=***###==============##@@========:*@@@@@============+----:**+++++*#=++
==#@##=***=##:+*==*=======##==###@========+#@@@@@===========:-----+********+++*
#==========#@=*===========#@+*#===========@@@@@@@@+========:-:----:==*****+++++
==*====*===*#=============#@@*##==========@@@@@@@=============::---*=**********
==***++*=====#=#:::::::*###@####*-:::-*===#@@*-@@@:--=#===#=+---:--:#=#===*+++*
------*=======*==+:--:####@##==##*---:#==@@@#*--@@=--:#####:-----:---+#======:-
---*#=====#*:++=**+-=######@#++=@##+-*=#@@@###:-+@@@--:##:--:#=--------+##===##
+=#====##+:*+::+==+:+=**=#@*:****##+-=#@@@@@@#+*+=@@#------#@@@@+--------+#####
##=#+::++:------:=####==##+-----:++**#@@@@#===:--+#@##::-*@#####@#+--------+###
####=:---------=###===###+----------=##=======+---------:+===######:--:------+#
#@*-:---::---+#=#=#=####+----------:#######==#*-----------=#===#====+----------
*-::::::---:###==###=##*-----------*#====#=##=#------------#####======:--------
::::::::--=######=####*------------#=#=####=#=#:-----------:######===##+-------
:::::---+@###########+------::----+##=#===#=###:------------:#######=###=------',
                     ],
                     9 => [
                         'album_id' => 9,
                         'image' => 'WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW:@:+#:W+#-@*:+-*WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
WWWWWWW#------.-:==:++*=*=*=***=*=**=:*#*********=##=########=********#WWWWWWWW
WWWWWWW#.......:#*-:++:+*==*#===*==*===#=======#WWW@@WWWWWWWW@#*======#WWWWWWWW
WWWWWWW#......-*+-:+*:*****==*=#**=*=*=#=====#WWWWWW@@@@@##=@#==*=====#WWWWWWWW
WWWWWWW#......+*+::::++==***=*=##**=+==#====#WWWWWW@@@@#@@@@@@@@###===#WWWWWWWW
WWWWWWW#.....---*:::+:::+***=**=#**=+*=#====WWWWWWW@@###@W@WWWWWWWW#==#WWWWWWWW
WWWWWWW#....--++-::::++:+**===*=#****==#===#WWWWWW@@@@=:+*====@WW@@@==#WWWWWWWW
WWWWWWW#....+*=:+=#=*:+***=####@=##*==##===#WWWWW@@@WW@*:+##=+:@W@@@=##WWWWWWWW
WWWWWWW#:++:*==*+:::::++*===#@@#@@##*###===#WWWWW######+-:**:-:@@W@@#=#WWWWWWWW
WWWWWWW#*=***+#*:*=*#*+++*==#@##@@@=@@##====WWWW@#####@*-::----@W@@#=##WWWWWWWW
WWWWWWW#======##=*#@#++**#@#***===##W@##*#===@WWW@@#@@@@*:+:--*WWW@==##WWWWWWWW
WWWWWWW##@@#@#=@=+:**++++++++*=###@#=#=#WWW@@**+==:-:======:+*@WW@#####WWWWWWWW
WWWWWWW#:*+=-+****+::::::++*=#=**=##@#==#@@@=#:-::::.+@@#++##@@@@#@####WWWWWWWW
WWWWWWW=-+++..:*=-:::+***========##@@##===#@WWW@#@W@WWWW#=#@@@W@#@W@###WWWWWWWW
WWWWWWW=:++:...-:--==######==**=##@@#######@WWWWW@@WWWWWW@WWW#=W@@WW@##WWWWWWWW
WWWWWWW=:++:....:++*#@@WW#====**==#==###@@WWWWWWW@@@WWWWWWW#::+#WWWW@W#WWWWWWWW
WWWWWWW#:++:--:::+*=#==###**********=##################=*+*+++++====**#WWWWWWWW
WWWWWWW#....-+**::::+######===**:.....*=.......:*##===#=*++-..........=WWWWWWWW
WWWWWWW#....:##:::::+##*##@####==:....+=.....+=@###=##@==****+........=WWWWWWWW
WWWWWWW#....-@*+::::::+*=##@##@#==:...+=...-=#@@@#@@#**=*=**=+++......=WWWWWWWW
WWWWWWW=....+=+*+:***=*+==#@#@###=*...*=..-=@W##@@@@@@###=*===*++.....#WWWWWWWW
WWWWWWW=...-*#:::+*++::::=##@@@@###:..*=.-=@W@@@*=@@@==@@###=*=**:-...#WWWWWWWW
WWWWWWW=..-*@*:*#@=+::::+=####@####*..*=.*#@@#+*::+***++@@@@@@@#==*-..#WWWWWWWW
WWWWWWW=.-+=@=*=*@@*:::++#@WWW@@@@#=-.*=.:@W#::::::++::::=WW@@#@#=#*..#WWWWWWWW
WWWWWWW=..-.*@=+++*=@***=@WWWW@@@@@#:-*=-:=W=:=*====*#+::+*@@W@@@#@=-.#WWWWWWWW
WWWWWWW=-----#++::++*===#WWWWW@@#@#*+.*=...=@+#**+++=@@+++*@@@#@@@=#:.#WWWWWWWW
WWWWWWW=::::-=@*==##########@W@@@@@=-.*=..:WW#+*##***:++++=@@@@@@@#=-.#WWWWWWWW
WWWWWWW#===*++=@@@@@@#####=##@@W@@#=:.*=...+WW=::+*++::::*#@WWWW@@#=--#WWWWWWWW
WWWWWWW#@@@@@@##@###@########@@@@@@+:.*=....=#W*::::::++***#WWWWW@=+-.#WWWWWWWW
WWWWWWW#@@@@@@@@@@W@@@@@@@@@@@@@@@@@*-*=...:##*.-+***==++**@WWWW@@#-..#WWWWWWWW
WWWWWWW#@@@@WWWWWWWWWWWWWW@@@@@@@@#=====....-:.....+#==*+*=WWWWW@*....#WWWWWWWW
WWWWWWW#@@@@WWWWWWWWWWWWWWWWWWWW@@@@##==-----........:=**+*W++:.......#WWWWWWWW
WWWWWWW@=###############################***************======*********@WWWWWWWW
WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW
WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW',
                     ],
                     10 => [
                         'album_id' => 10,
                         'image' => '+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
++++++++++++++++++++++++++++++++*==***=*==*==***+++++++++++++++++++++++++++++++
+++++++***+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
+++++++====#*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
+++++*===*===+++++++*##=*=*++****##=++++++=+=*+++++++++++++++++++++++++++++++++
++*#########*#=####*#=*####*+*###*==*###**#====*=*+*==*====*=====*=*==*=*==++++
+++++++=###=*#=#*==*##+###*+++*##=#=======#==#*#=*#=#*=#=#======#==*=*==##=*+++
+++++++=####*===#*#*##*###=***###=*=+*=*+*****#*=**==*=***=**==***==*+**==#*+++
+++++++#####=+**=*+*##*##########=++++++++++++++++++++++++++++++++*********++++
++++++*=####*++++++++++=###***###*+++++++++++++++++++++++++++++++********=**+++
+++++#=#*###=++++++++++*##*+++=##++++++++++++++++++++++++++++++++++++++*****+++
+++++=***####=++++++*#=*##**++=##===:::--::+*===**+++++++++++++++++++++++++++++
++++++=#####=+++++++*###==##=*#*###+:--::::+++:+*===*++++++++++++++++++++++++++
+++++++++++++++++++++++*==**+++***+:+:------:::++**++*=*+++++++++++++++++++++++
+++++++++++++++++++++*=+--:*+:+::+--::::----:::-:++++:::**+++++++++++++++++++++
+++++++++++++++++++*=+++*:=++++**++::+:+::---::--:++++:::+=*+++++++++++++++++++
++++++++++++++++++*==+*=***++*+*******+:**++:++*****+:::+++=*++++++++++++++++++
+++++++++++++++++*====*+:+*=*=+++****==#@@@@#=*****++++*++**==+++++++++++++++++
++++++++++++++++*=#*=+***=**:=#@@#**=#@W#=*=@@#=*+====*+++**+**++++++++++++++++
++++++++++++++++==*****===*=@W@WWWW###@W*+=+=@=**##=::#=+:::::=*+++++++++++++++
+++++++++++++++**+=**===###@@+*#@WW@#===###==****#@#=::++:--:-+=+++++++++++++++
+++++++++++++++****===#=#=###+##WW#=*=******==*****=*=+:+-:-:--**++++++++++++++
+++++++++++++++=*====##****#@@@@@#=#***+****++*+**==*==*+-::---**++++++++++++++
+++++++++++++++==#####=****=#*==*****+:::+++*+:*****+=:::-::::+**++++++++++++++
+++++++++++++++*==####******=***==#+*:---:+::-+*:**:+++++::-::*=*++++++++++++++
+++++++++++++++*=#==#=*+=****#=##@#+:----:+:---+:+**+++++:::++*=+++++++++++++++
++++++++++++++++=####=*=****=####@##----:++----:+:+:+:::**:-+:=*+++++++++++++++
++++++++====++++*=###=***+===@@@##=+----::::---++:++**+:+++:*+*++++*==*++++++++
+++++++*==+****++*=@#*=**=#==##==##*:::::::----:+:::=**+*++***++***+*==*+++++++
++++++*=====*==*++*=@+***@=*##=*===*::*+::+:+:-:::+=##==*===*++==*+=**==*++++++
++++*=*+*===*=====++****=*====*=#==+++**::+:+++++++=++::++*****====+*====*+++++
++++==**==========*++*=*====*#=#=##++@=**++*=:::++==++++**+*=*====*=*=+*==*++++
++++*======*===**===*++**=**=##=*=@@#==##=*++:+=*+@=++**++**==*+*=*=*=====*++++
++++*========*====****==*+**=####===*=**####==##===**++**===*==*+==*======+++++
++++++***======*+++**===*=**++***==*=====@#=*****++++*=*=====*===*+*=+***++++++
++++++++++==*==*+*==**+*===*=*=*+**++++++++++++*====*=**=*==+=*=====*++++++++++
++++++++++++**====*=***==*===*=**=*=**=====*===**==*===+**=*++***++++++++++++++
+++++++++++++++++++*====**===*====*=****==*=*=**===*=*=***=++++++++++++++++++++
+++++++++++++++++++++++++++++++++++****=**===*==+++****++++++++++++++++++++++++
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++',
                     ],
                     11 => [
                         'album_id' => 11,
                         'image' => '************++++++++++++******+++:::+::----::+::::+******+++++++++*+***********
****************+++++++++++*+++:--:****+++*+**+--:+++++++++++++++**************
***************++***+::::::::--:***++::-::++*++**+--+:::++++++*+***************
++++************++++++++:::-.:***+::+++++++++:+:++*+--:::+**+*++************+++
+++++++++++++++**+++++++++-.+*+*-:++::::::::++++:++++-:+++**++*****+++:++++++++
++++++++++++++::::::::+++:-:*++:+::::--:-::-::++:+:+++-:**++::::+:+++++:+++++++
++++++++++++++++:::::::::--+++::+++:::::::+::::+++:++*---::::::::+++::+++++++++
+++++++++++++++::::::::::--***+++:::-::::-::-:-:++::++:.-:::::::+++++++++++++++
**********+++++++++++++++:-*++-:::::::::::::::+++++++*--++*********************
**********+++++++++++++++:-:*++-++:+:::+::+::++::+:+++.-+**********************
************+++***++::::::--:*::+:+#@@#*===#@@@#*-:+*-.::::::++****************
***********+++++:++++:::::=#@@#*:@+:---+=**-.-:++#*#==#@@+::+:++++++++*********
****+++++++++++++++=@#@#@#:.-:-*#=+:-+...:..*--:+##:...-*##@*=##+:+++++++++++**
+++++++++++++++*=#@+..-+@*-+...::#*:--......:.-:##--..:+@=:..-:@@##@=++++++++++
+++++++++++++@=+++:=::..+**#*:-..-*=+:-.....-+*#:....:#:-...::#:----#++++++++++
+++++++++++:*#+*:.-:+::++*+-.:*#*:--+++=##=*++:--+:+*+++::**::::++==.--:++:***+
++++++++=+..=**##*:+=*++:*=@WWWWWWWWWWWWWWWWWWWW@WWWW@#+++:#+::=+:+#+.-++=*+**=
++:::+:+*.-++::*++:+**++=@@WWWWWWWWWWWWWWWWWWWWWWWWWWWW@=+++**:++*++*:--**+*+++
*+++++::.--+++:++++*+:::*WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW@:::=+:-++:+-:::-:::-::
**+::+++-.-*-+##*==@#---+WWWWWWWWWWWWWWWW@WWWWWWWW@*:-#W#---+--.+=::*+::=*+:*++
**+:+:++*:+*+=**:++*=*::=+=*=WWWWWW@#+-.--:*WWWWW@@=+=*##+*##=:*@#==++:*::---..
*+====#*-+:+:-::+*#**::-:+::*WWWWWWW=#=***#==@WWW@+----+-:+-::+-==*#*::--**:+::
:*:*:=++...-++*==*:+=::*+:+.+W@WWWWW*-+::+.-+@WWW@#-.::==*==-+-+@*+:....+*=:**:
+*:+:=++-..*=*=*:+==*:+--:=-.-#@W@WW@****=:-+=#W@++-..:*==+:**+++:++...:++*:==+
*++@*++++.-*+==:::+:::::+*=*:*..-:++@=*=#*::==+++---*+:+**++:-:=*+:::.:+::+=+*=
*=++**=+++++*==:+++:+*--:*-:+#*=*+.-**=*+*=+**+:*=*=@=-:---+-:+-=#*++::----:=*+
+:::**+-*+=#+=*:++-.-:::**+#:+***=#++=++::+++=*:+#**+:*+:*+:++++++++:+++::+=+==
=:*.*:+.***:*:++++-.-*++*=+:#+*+**=+:+**+++**+:++#=-+:++==*+:+--++:++:+*:+**+@#
*-*.-*-:+=+:=-:+++--*+**++*++*+*+:+**:**++++++++.-+++*:*:*==:*-:*+--*:**:=*:+++
+-:+-:-*****#.-*+*:-*:***:+=:==*+++*+:::+::+::-+.-+-++++:=:+-::=+:--+*+-++++:.-
-++++*+*=+=#=--:+**:--:+*+:=+*===*==*--::*::--::.:=+**++*+*---+*+-:=*+-+*+++..:
.:+++=++#:-*==+-::**-::#*+*+*=#==+**=+::***+++*-.*=***++*-::*+*:-:=+:::+***++-*
:.+++**:=*--***=:-++=*::+++*==*:-:*:+*++:+*-**+.-=*=**:::-**++*::*:-::*+**:+**+
+-.+++#+=*:-:--:*=**+*-++:+*+---:**##*-+:++:+***#===*+*++*----++:=+:*+*+::*+:*+
++-.+-::-+*++*:*+:---:*:-*+++--:##+*#*+*=***=:**#===:-.:*:+::+++:++++:::-++:*+*
+++-.:+=*--:++*=*+=*+:*:-==**+=#==:=@=*=**+***:*#===:..-**+---++-::+:-++**+**+:
++++-.:*=:---:--*=*+:--++:**=*=**=-.-+***+:*++-.:*=*+---+:**:--:+*+=****++:---+
+++++--+#++*=+++****+::-:*+*:***==:.:+*+:**-+*..:#*=*::+:+--+-++:=:-:-----+*::+
++++++-::--:++:+::++*==*-:**=+==+=-.-=::*-:**=-.+*+=**=-+*+:-+*+-+***:*=***::*+
+++++:::***----+-:::+++++*=:***+=+--+:=#:--+*+:*#:++***=-*+*:*:*++---:+:::++:**',
                     ],
                     12 => [
                         'album_id' => 12,
                         'image' => '========================###========#===========================================
=======================####=================********===========================
=========================##=================**+*++++****===================*===
===========================#===============**++++++++++++**====================
===============================#=========*+++::+++++********==================#
=======================================*+++::::::::::++**===*===##============#
=====================================**+++++++:::---:++******+**===============
==============####==================****++++++++++::+++**++++++**=============*
==============###==================**+++++++++::++*******++::++**==============
=======================#=========*+++++++++++:::::+++****+++:+***==============
================================*++++++++++::--::::::::++:::::++**=============
===============================**+++++++++:::---::::::::::::::::::+*===========
============================***+++++++++::::-------::::++++++***+:-:+*=======**
======##=========****++*====**+++++++++:::--------::::::++++*====*++**=========
==#===####======*+++++:+*=**+++++++++++::-------::::::::::::++++*==**==========
==##==##====#=====***=******++++++++:::::-----::::::::::++++++++*=============#
======================*****+++++++::::::::-::::::::::::+++::+******===========#
===============***********+++++++::::::::::::::::+::::++****+:+*==*==========##
===============*****+++**++++++++:::::::::::::::::::++*=====*+--****=======####
==============**++++++++*+++++++++++::::::::::::::::::+*=======+**===##########
=============**+++++++++**++++++++++::::::::::::++::::::++**===++**=###########
####========**++++:+++++***+++++++++++::::::::::::::--:::::++*=***==###########
###=======***++++::::::+****=***++++++++::++++::::------::::::***=#############
##===*******+++::::::::+++*********++++++++++++:::-----:+++++****=##===########
@#==**+++++++++::::::::::+++***********++++++++::::::::++********=#############
@@@#=+++++++++:::::::---:+++++************++++++:::::::++**====*=##############
@@@@##*+++++++:::::::::::++++++**************++++:::::+++****===###############
@@@@@##=++++++::::::::::::++++++++*********=*****+++::+*******=################
@@@@@@@##*++++::::::::::::++++++++++**==========************=##################
@@@@@@@@##+++:::::::::::::+++++::::+++*============********=##@################
@@@@@@@@@#=++::::::::::::+++++++++++++*============********#@@#################
@@@@@@@@@@@=+::::::::::::+++++++*******===============*===#@###################
@@@@@@@@@@@#=+::::::::::+++++***=====================#####@####################
@@@@@@@@@@@@#*++::::::::+++**===========##############@###@####################
@@@@WWWWWWW@@=*++:::::++**===========#@@@@###############@@####################
@@WWWWWWWWWWW#*++++++***===========#@@@@@@##########@@##@#############@########
@@WWWWWWWWWWW@=*+*****========**==#@@@@@@@@@###@#@#@#@#@@@@###@#####@##########
@WW@WWWWWWWWWW#*****=**===*****===@@@@@@@@####@@###@@@@#@#@@@@##@#@############
@@@@@@@WWW@@@@@=**==*****+***====@@@@@@@@@@@@#######@###@###@###@##############
@@@@@@@@@@@@@@@=****+++******==#@#@@#@#@@@@@###################################',
                     ],
                 ] as $data) {
            AlbumCover::create($data);
        }

        // Seed Groupies
        foreach ([
                     1 => ['name' => 'Bianca Perez-Mora Macias', 'person_id' => 1],
                     2 => ['name' => 'Jerry Hall', 'person_id' => 1],
                     3 => ['name' => 'Marsha Hunt', 'person_id' => 1],
                     4 => ['name' => 'Marianne Faithfull', 'person_id' => 1],
                     5 => ['name' => 'Carla Bruni', 'person_id' => 1],
                     6 => ['name' => 'Anita Pallenberg', 'person_id' => 2],
                     7 => ['name' => 'Patti Hansen', 'person_id' => 2],
                 ] as $data) {
            Groupie::create($data);
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            JoryServiceProvider::class,
        ];
    }

    protected function registerJoryBuilders()
    {
        /**
         * Register some JoryBuilders and let some of them be discovered by de autoRegistrar.
         */
        Jory::register(BandJoryResource::class);
        Jory::register(PersonJoryResource::class);
        Jory::register(SongJoryResourceWithBeforeQueryBuildFilterHook::class);
        Jory::register(SongJoryResourceWithAfterFetchHook::class);
        Jory::register(SongJoryResource::class);
    }

    public function assertQueryCount($expected)
    {
        $this->assertEquals($expected, count(\DB::getQueryLog()));
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('jory.auto-registrar', [
            'namespace' => 'JosKolenberg\LaravelJory\Tests\JoryResources',
            'path' => __DIR__ . '/JoryResources',
        ]);
        $app['config']->set('jory.generator', [
            'namespace' => 'JosKolenberg\LaravelJory\Tests\Models',
            'path' => __DIR__ . '/Models',
        ]);
    }
}
