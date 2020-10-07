<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect;

use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\Models\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\BandJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\MusicianJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\PersonJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Models\SubFolder\Album;
use JosKolenberg\LaravelJory\Tests\Models\Tag;
use JosKolenberg\LaravelJory\Tests\TestCase;

class ExplicitSelectTest extends TestCase
{
    /** @test */
    public function it_only_selects_requested_fields_when_using_explicit_select()
    {
        $joryResource = new UserJoryResource();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['description'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, User::query(), 'select `name`, `email` from `users`');
    }

    /** @test */
    public function it_selects_the_primary_key_field_when_no_fields_are_requested_to_prevent_query_errors()
    {
        $joryResource = new TeamJoryResource();

        $joryResource->setJory((new ArrayParser([
            'fld' => [],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Team::query(), 'select `teams`.`id` from `teams`');
    }

    /** @test */
    public function it_selects_the_primary_key_field_when_no_fields_are_requested_in_a_relation_to_prevent_query_errors()
    {
        $band = $this->seedBeatles();

        $joryResource = new MusicianJoryResource();

        $joryResource->setJory((new ArrayParser([
            'fld' => [],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $band->musicians(), 'select `musicians`.`id` from `musicians` inner join `band_members` on `musicians`.`id` = `band_members`.`musician_id` where `band_members`.`band_id` = ?');
    }

    /**
     * HAS ONE RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_hasOne_relation_using_explicit_select()
    {
        Jory::register(UserJoryResource::class);
        $joryResource = new TeamJoryResource();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'firstUser' => [],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Team::query(), 'select `teams`.`name`, `teams`.`id` from `teams`');
    }

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_hasOne_relation_using_explicit_select()
    {
        $team = $this->seedSesameStreet();
        $team = Team::find($team->id); // Convert the default Team to the specific Team class for this test.

        $joryResource = new UserJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $team->firstUser(), 'select `users`.`name`, `users`.`team_id` from `users` where `users`.`team_id` = ? and `users`.`team_id` is not null');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasOne_relation_using_explicit_select(
    )
    {
        $joryResource = new TeamJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['first_user_field'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Team::query(), 'select `teams`.`id` from `teams`');
    }

    /**
     * BELONGS TO RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_belongsTo_relation_using_explicit_select()
    {
        $joryResource = new UserJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'team' => [],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, User::query(), 'select `users`.`name`, `users`.`team_id` from `users`');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_belongsTo_relation_using_explicit_select()
    {
        $this->seedSesameStreet();
        $user = User::first();

        $joryResource = new TeamJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $user->team(), 'select `teams`.`name`, `teams`.`id` from `teams` where `teams`.`id` = ?');
    }

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_field_which_eager_loads_a_belongsTo_relation_using_explicit_select(
    )
    {
        $joryResource = new UserJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'team_name'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, User::query(), 'select `users`.`name`, `users`.`team_id` from `users`');
    }

    /**
     * HAS MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_hasMany_relation_using_explicit_select()
    {
        $joryResource = new TeamJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'users' => [
                    'fld' => ['name'],
                ],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Team::query(), 'select `teams`.`name`, `teams`.`id` from `teams`');
    }

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_hasMany_relation_using_explicit_select()
    {
        $team = $this->seedSesameStreet();
        $team = Team::find($team->id); // Convert the default Team to the specific Team class for this test.

        $joryResource = new UserJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $team->users(), 'select `users`.`name`, `users`.`team_id` from `users` where `users`.`team_id` = ? and `users`.`team_id` is not null');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasMany_relation_using_explicit_select(
    )
    {
        $joryResource = new TeamJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'users_string'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Team::query(), 'select `teams`.`name`, `teams`.`id` from `teams`');
    }

    /**
     * BELONGS TO MANY RELATIONS ===============================================================================================
     */

    public function it_adds_the_primary_key_field_when_requesting_a_belongsToMany_relation_using_explicit_select()
    {
        $builder = Person::query();

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

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `people`.`first_name`, `people`.`id` from `people`', $builder->toSql());
    }

    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_belongsToMany_relation_using_explicit_select(
    )
    {
        $builder = Person::find(1)->instruments();

        $joryResource = new InstrumentJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `instruments`.`name` from `instruments` inner join `instrument_person` on `instruments`.`id` = `instrument_person`.`instrument_id` where `instrument_person`.`person_id` = ?',
            $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_belongsToMany_relation_using_explicit_select(
    )
    {
        $builder = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['first_name', 'instruments_string'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `people`.`first_name`, `people`.`id` from `people`', $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_hasManyThrough_relation_using_explicit_select()
    {
        $builder = Band::query();

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

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $builder->toSql());
    }

    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_hasManyThrough_relation_using_explicit_select(
    )
    {
        $builder = Band::find(1)->songs();

        $joryResource = new SongJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['title'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `songs`.`title` from `songs` inner join `albums` on `albums`.`id` = `songs`.`album_id` where `albums`.`band_id` = ?',
            $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasManyThrough_relation_using_explicit_select(
    )
    {
        $builder = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'titles_string'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_hasOneThrough_relation_using_explicit_select()
    {
        $builder = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'firstSong' => [
                    'fld' => ['title'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $builder->toSql());
    }

    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_hasOneThrough_relation_using_explicit_select(
    )
    {
        $builder = Band::find(1)->firstSong();

        $joryResource = new SongJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['title'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `songs`.`title` from `songs` inner join `albums` on `albums`.`id` = `songs`.`album_id` where `albums`.`band_id` = ? order by `songs`.`id` asc',
            $builder->toSql());
    }

    public function it_returns_the_same_result_when_requesting_a_hasOneThrough_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'firstSong' => [
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

    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasOneThrough_relation_using_explicit_select()
    {
        $builder = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'first_title_string']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_morphOne_relation_using_explicit_select()
    {
        $builder = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['first_name'],
            'rlt' => [
                'firstImage' => [
                    'fld' => ['url'],
                ],
            ],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `people`.`first_name`, `people`.`id` from `people`', $builder->toSql());
    }
    
    public function it_adds_the_foreign_key_field_on_the_relation_query_when_requesting_a_morphOne_relation_using_explicit_select(
    )
    {
        $builder = Person::find(1)->firstImage();

        $joryResource = new ImageJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['url'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `images`.`url`, `images`.`imageable_id` from `images` where `images`.`imageable_id` = ? and `images`.`imageable_id` is not null and `images`.`imageable_type` = ?',
            $builder->toSql());
    }

    public function it_returns_the_same_result_when_requesting_a_morphOne_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['first_name'],
            'rlt' => [
                'firstImage' => [
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

    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphOne_relation_using_explicit_select()
    {
        $builder = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['first_name', 'first_image_url']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `people`.`first_name`, `people`.`id` from `people`', $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_morphMany_relation_using_explicit_select()
    {
        $builder = Band::query();

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

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $builder->toSql());
    }

    public function it_adds_the_foreign_key_field_on_the_relation_query_when_requesting_a_morphMany_relation_using_explicit_select(
    )
    {
        $builder = Band::find(1)->images();

        $joryResource = new ImageJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['url'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `images`.`url`, `images`.`imageable_id` from `images` where `images`.`imageable_id` = ? and `images`.`imageable_id` is not null and `images`.`imageable_type` = ?',
            $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphMany_relation_using_explicit_select()
    {
        $builder = Band::query();

        $joryResource = new BandJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'image_urls_string']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `bands`.`name`, `bands`.`id` from `bands` limit 30', $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_morphToMany_relation_using_explicit_select()
    {
        $builder = Album::query();

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

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `albums`.`name`, `albums`.`id` from `albums`', $builder->toSql());
    }

    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_morphToMany_relation_using_explicit_select(
    )
    {
        $builder = Album::find(1)->tags();

        $joryResource = new TagJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `tags`.`name` from `tags` inner join `taggables` on `tags`.`id` = `taggables`.`tag_id` where `taggables`.`taggable_id` = ? and `taggables`.`taggable_type` = ?',
            $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphToMany_relation_using_explicit_select()
    {
        $builder = Album::query();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'tag_names_string']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `albums`.`name`, `albums`.`id` from `albums`', $builder->toSql());
    }

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

    public function it_adds_the_foreign_key_field_when_requesting_a_morphedByMany_relation_using_explicit_select()
    {
        $builder = Tag::query();

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

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `tags`.`name`, `tags`.`id` from `tags`', $builder->toSql());
    }

    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_morphedByMany_relation_using_explicit_select(
    )
    {
        $builder = Tag::find(1)->albums();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `albums`.`name` from `albums` inner join `taggables` on `albums`.`id` = `taggables`.`taggable_id` where `taggables`.`tag_id` = ? and `taggables`.`taggable_type` = ?',
            $builder->toSql());
    }

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

    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphedByMany_relation_using_explicit_select()
    {
        $builder = Tag::query();

        $joryResource = new TagJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'song_titles_string']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($builder);

        $this->assertEquals('select `tags`.`name`, `tags`.`id` from `tags`', $builder->toSql());
    }

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

    protected function assertJoryResourceGeneratesQuery(JoryResource $joryResource, $baseQuery, string $string)
    {
        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($baseQuery);

        $this->assertEquals($string, $baseQuery->toSql());
    }

}
