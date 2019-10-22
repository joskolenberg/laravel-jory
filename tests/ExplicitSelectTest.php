<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\AlbumCoverJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\AlbumJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\BandJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\ImageJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\InstrumentJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\PersonJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\TagJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Models\Tag;

class ExplicitSelectTest extends TestCase
{
    /** @test */
    public function it_only_selects_requested_fields_when_using_explicit_select()
    {
        $query = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['full_name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `first_name`, `last_name` from `people`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_fields_using_explicit_select()
    {
        $jory = [
            'fld' => ['full_name', 'date_of_birth'],
        ];

        $expected = $this->json('GET', 'jory/person/3', ['jory' => $jory])->getContent();
        Jory::register(PersonJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/person/3', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);
    }

    /**
     * HAS ONE RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_hasOne_relation_using_explicit_select()
    {
        $query = Album::query();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'cover' => [],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`name`, `albums`.`id` from `albums`', $query->toSql());
    }

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_hasOne_relation_using_explicit_select()
    {
        $query = Album::find(1)->cover();

        $joryResource = new AlbumCoverJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['image'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `album_covers`.`image`, `album_covers`.`album_id` from `album_covers` where `album_covers`.`album_id` = ? and `album_covers`.`album_id` is not null',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_hasOne_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'cover' => [
                    'fld' => ['image'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        Jory::register(AlbumCoverJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasOne_relation_using_explicit_select(
    )
    {
        $query = Album::query();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['cover_image'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`id` from `albums`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_hasOne_relation_using_explicit_select(
    )
    {
        $jory = [
            'fld' => ['name', 'cover_image'],
        ];

        $expected = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * BELONGS TO RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_belongsTo_relation_using_explicit_select()
    {
        $query = Song::query();

        $joryResource = new SongJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['title'],
            'rlt' => [
                'album' => [],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `songs`.`title`, `songs`.`album_id` from `songs`', $query->toSql());
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_belongsTo_relation_using_explicit_select()
    {
        $query = Song::find(1)->album();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`name`, `albums`.`id` from `albums` where `albums`.`id` = ?',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_belongsTo_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['title'],
            'rlt' => [
                'album' => [
                    'fld' => ['name'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/song', ['jory' => $jory])->getContent();
        Jory::register(SongJoryResourceWithExplicitSelect::class);
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/song', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_field_which_eager_loads_a_belongsTo_relation_using_explicit_select(
    )
    {
        $query = Song::query();

        $joryResource = new SongJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['title', 'album_name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `songs`.`title`, `songs`.`album_id` from `songs`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_belongsTo_relation_using_explicit_select(
    )
    {
        $jory = [
            'fld' => ['title', 'album_name'],
        ];

        $expected = $this->json('GET', 'jory/song/3', ['jory' => $jory])->getContent();
        Jory::register(SongJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/song/3', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * HAS MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_hasMany_relation_using_explicit_select()
    {
        $query = Album::query();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'songs' => [
                    'fld' => ['title'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`name`, `albums`.`id` from `albums`', $query->toSql());
    }

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_hasMany_relation_using_explicit_select()
    {
        $query = Album::find(1)->songs();

        $joryResource = new SongJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['title'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `songs`.`title`, `songs`.`album_id` from `songs` where `songs`.`album_id` = ? and `songs`.`album_id` is not null',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_hasMany_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'songs' => [
                    'fld' => ['title'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/album/4', ['jory' => $jory])->getContent();
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        Jory::register(SongJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/album/4', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasMany_relation_using_explicit_select(
    )
    {
        $query = Album::query();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'titles_string'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`name`, `albums`.`id` from `albums`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_hasMany_relation_using_explicit_select(
    )
    {
        $jory = [
            'fld' => ['name', 'titles_string'],
        ];

        $expected = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * BELONGS TO MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_belongsToMany_relation_using_explicit_select()
    {
        $query = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['first_name'],
            'rlt' => [
                'instruments' => [
                    'fld' => ['name'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `people`.`first_name`, `people`.`id` from `people`', $query->toSql());
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_belongsToMany_relation_using_explicit_select(
    )
    {
        $query = Person::find(1)->instruments();

        $joryResource = new InstrumentJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `instruments`.`name` from `instruments` inner join `instrument_person` on `instruments`.`id` = `instrument_person`.`instrument_id` where `instrument_person`.`person_id` = ?',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_belongsToMany_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['first_name'],
            'rlt' => [
                'instruments' => [
                    'fld' => ['name'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/person/4', ['jory' => $jory])->getContent();
        Jory::register(PersonJoryResourceWithExplicitSelect::class);
        Jory::register(InstrumentJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/person/4', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_belongsToMany_relation_using_explicit_select(
    )
    {
        $query = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['first_name', 'instruments_string'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `people`.`first_name`, `people`.`id` from `people`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_belongsToMany_relation_using_explicit_select(
    )
    {
        $jory = [
            'fld' => ['first_name', 'instruments_string'],
        ];

        $expected = $this->json('GET', 'jory/person/10', ['jory' => $jory])->getContent();
        Jory::register(PersonJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/person/10', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * HAS MANY THROUGH RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_hasManyThrough_relation_using_explicit_select()
    {
        $query = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'songs' => [
                    'fld' => ['title'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $query->toSql());
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_hasManyThrough_relation_using_explicit_select(
    )
    {
        $query = Band::find(1)->songs();

        $joryResource = new SongJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['title'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `songs`.`title` from `songs` inner join `albums` on `albums`.`id` = `songs`.`album_id` where `albums`.`band_id` = ?',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_hasManyThrough_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'songs' => [
                    'fld' => ['title'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/band/4', ['jory' => $jory])->getContent();
        Jory::register(BandJoryResourceWithExplicitSelect::class);
        Jory::register(SongJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/band/4', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasManyThrough_relation_using_explicit_select(
    )
    {
        $query = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'titles_string'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_hasManyThrough_relation_using_explicit_select(
    )
    {
        $jory = [
            'fld' => ['name', 'titles_string'],
        ];

        $expected = $this->json('GET', 'jory/band', ['jory' => $jory])->getContent();
        Jory::register(BandJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/band', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * HAS ONE THROUGH RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_hasOneThrough_relation_using_explicit_select()
    {
        $query = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'first_song' => [
                    'fld' => ['title'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $query->toSql());
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_hasOneThrough_relation_using_explicit_select(
    )
    {
        $query = Band::find(1)->firstSong();

        $joryResource = new SongJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['title'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `songs`.`title` from `songs` inner join `albums` on `albums`.`id` = `songs`.`album_id` where `albums`.`band_id` = ? order by `songs`.`id` asc',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_hasOneThrough_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'first_song' => [
                    'fld' => ['title'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/band/4', ['jory' => $jory])->getContent();
        Jory::register(BandJoryResourceWithExplicitSelect::class);
        Jory::register(SongJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/band/4', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasOneThrough_relation_using_explicit_select()
    {
        $query = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'first_title_string']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_hasOneThrough_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name', 'first_title_string']
        ];

        $expected = $this->json('GET', 'jory/band', ['jory' => $jory])->getContent();
        Jory::register(BandJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/band', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * MORPH ONE RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_morphOne_relation_using_explicit_select()
    {
        $query = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['first_name'],
            'rlt' => [
                'first_image' => [
                    'fld' => ['url'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `people`.`first_name`, `people`.`id` from `people`', $query->toSql());
    }
    
    /** @test */
    public function it_adds_the_foreign_key_field_on_the_relation_query_when_requesting_a_morphOne_relation_using_explicit_select(
    )
    {
        $query = Person::find(1)->firstImage();

        $joryResource = new ImageJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['url'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `images`.`url`, `images`.`imageable_id` from `images` where `images`.`imageable_id` = ? and `images`.`imageable_id` is not null and `images`.`imageable_type` = ?',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_morphOne_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['first_name'],
            'rlt' => [
                'first_image' => [
                    'fld' => ['url'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/person/4', ['jory' => $jory])->getContent();
        Jory::register(PersonJoryResourceWithExplicitSelect::class);
        Jory::register(ImageJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/person/4', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphOne_relation_using_explicit_select()
    {
        $query = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['first_name', 'first_image_url']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `people`.`first_name`, `people`.`id` from `people`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_morphOne_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['first_name', 'first_image_url']
        ];

        $expected = $this->json('GET', 'jory/person/1', ['jory' => $jory])->getContent();
        Jory::register(PersonJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/person/1', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * MORPH MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_morphMany_relation_using_explicit_select()
    {
        $query = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'images' => [
                    'fld' => ['url'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $query->toSql());
    }

    /** @test */
    public function it_adds_the_foreign_key_field_on_the_relation_query_when_requesting_a_morphMany_relation_using_explicit_select(
    )
    {
        $query = Band::find(1)->images();

        $joryResource = new ImageJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['url'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `images`.`url`, `images`.`imageable_id` from `images` where `images`.`imageable_id` = ? and `images`.`imageable_id` is not null and `images`.`imageable_type` = ?',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_morphMany_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'images' => [
                    'fld' => ['url'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/band/4', ['jory' => $jory])->getContent();
        Jory::register(BandJoryResourceWithExplicitSelect::class);
        Jory::register(ImageJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/band/4', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphMany_relation_using_explicit_select()
    {
        $query = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'image_urls_string']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_morphMany_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name', 'image_urls_string']
        ];

        $expected = $this->json('GET', 'jory/band', ['jory' => $jory])->getContent();
        Jory::register(BandJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/band', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * MORPH TO MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_morphToMany_relation_using_explicit_select()
    {
        $query = Album::query();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'tags' => [
                    'fld' => ['name'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`name`, `albums`.`id` from `albums`', $query->toSql());
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_morphToMany_relation_using_explicit_select(
    )
    {
        $query = Album::find(1)->tags();

        $joryResource = new TagJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `tags`.`name` from `tags` inner join `taggables` on `tags`.`id` = `taggables`.`tag_id` where `taggables`.`taggable_id` = ? and `taggables`.`taggable_type` = ?',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_morphToMany_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'tags' => [
                    'fld' => ['name'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/album/4', ['jory' => $jory])->getContent();
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        Jory::register(TagJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/album/4', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphToMany_relation_using_explicit_select()
    {
        $query = Album::query();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'tag_names_string']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`name`, `albums`.`id` from `albums`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_morphToMany_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name', 'tag_names_string']
        ];

        $expected = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /**
     * MORPHED BY MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_morphedByMany_relation_using_explicit_select()
    {
        $query = Tag::query();

        $joryResource = new TagJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'albums' => [
                    'fld' => ['name'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `tags`.`name`, `tags`.`id` from `tags`', $query->toSql());
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_morphedByMany_relation_using_explicit_select(
    )
    {
        $query = Tag::find(1)->albums();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`name` from `albums` inner join `taggables` on `albums`.`id` = `taggables`.`taggable_id` where `taggables`.`tag_id` = ? and `taggables`.`taggable_type` = ?',
            $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_morphedByMany_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'songs' => [
                    'fld' => ['title'],
                ],
            ],
        ];

        $expected = $this->json('GET', 'jory/tag/1', ['jory' => $jory])->getContent();
        Jory::register(TagJoryResourceWithExplicitSelect::class);
        Jory::register(SongJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/tag/1', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphedByMany_relation_using_explicit_select()
    {
        $query = Tag::query();

        $joryResource = new TagJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'song_titles_string']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `tags`.`name`, `tags`.`id` from `tags`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_morphedByMany_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name', 'song_titles_string']
        ];

        $expected = $this->json('GET', 'jory/tag', ['jory' => $jory])->getContent();
        Jory::register(TagJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/tag', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

}

// In ieder gaval 1 veld selecten als er niets is geselecteerd