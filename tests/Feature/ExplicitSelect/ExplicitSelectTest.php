<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect;

use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Musician;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Tag;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\ImageJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\SongJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\Models\Band;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\Models\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\BandJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\MusicianJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\ExplicitSelect\JoryResources\TagJoryResource;
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

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_belongsToMany_relation_using_explicit_select()
    {
        $joryResource = new MusicianJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'bands' => [
                    'fld' => ['name'],
                ],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Musician::query(), 'select `musicians`.`name`, `musicians`.`id` from `musicians`');
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_belongsToMany_relation_using_explicit_select(
    )
    {
        $band = $this->seedBeatles();

        $joryResource = new MusicianJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $band->musicians(), 'select `musicians`.`name` from `musicians` inner join `band_members` on `musicians`.`id` = `band_members`.`musician_id` where `band_members`.`band_id` = ?');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_belongsToMany_relation_using_explicit_select(
    )
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'musicians_string'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /**
     * HAS MANY THROUGH RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_hasManyThrough_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'songs' => [
                    'fld' => ['title'],
                ],
            ],
        ]))->getJory());


        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_hasManyThrough_relation_using_explicit_select(
    )
    {
        $band = $this->seedBeatles();

        $joryResource = new SongJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['title'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $band->songs(), 'select `songs`.`title` from `songs` inner join `albums` on `albums`.`id` = `songs`.`album_id` where `albums`.`band_id` = ?');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasManyThrough_relation_using_explicit_select(
    )
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'songs_string'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /**
     * HAS ONE THROUGH RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_hasOneThrough_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'firstSong' => [
                    'fld' => ['title'],
                ],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_hasOneThrough_relation_using_explicit_select(
    )
    {
        $band = $this->seedBeatles();
        $band = Band::find($band->id); // Convert the default Band to the specific Band class for this test.

        $joryResource = new SongJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['title'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $band->firstSong(), 'select `songs`.`title` from `songs` inner join `albums` on `albums`.`id` = `songs`.`album_id` where `albums`.`band_id` = ?');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasOneThrough_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'first_title_string']
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /**
     * MORPH ONE RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_morphOne_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'firstImage' => [
                    'fld' => ['url'],
                ],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /** @test */
    public function it_adds_the_foreign_key_field_on_the_relation_query_when_requesting_a_morphOne_relation_using_explicit_select(
    )
    {
        $band = $this->seedBeatles();
        $band = Band::find($band->id); // Convert the default Band to the specific Band class for this test.

        $joryResource = new ImageJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['url'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $band->firstImage(), 'select `images`.`url`, `images`.`imageable_id` from `images` where `images`.`imageable_id` = ? and `images`.`imageable_id` is not null and `images`.`imageable_type` = ?');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphOne_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'first_image_url']
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /**
     * MORPH MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_morphMany_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'images' => [
                    'fld' => ['url'],
                ],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /** @test */
    public function it_adds_the_foreign_key_field_on_the_relation_query_when_requesting_a_morphMany_relation_using_explicit_select(
    )
    {
        $band = $this->seedBeatles();
        $band = Band::find($band->id); // Convert the default Band to the specific Band class for this test.

        $joryResource = new ImageJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['url'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $band->images(), 'select `images`.`url`, `images`.`imageable_id` from `images` where `images`.`imageable_id` = ? and `images`.`imageable_id` is not null and `images`.`imageable_type` = ?');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphMany_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'image_urls_string']
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /**
     * MORPH TO MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_morphToMany_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'tags' => [
                    'fld' => ['name'],
                ],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_morphToMany_relation_using_explicit_select(
    )
    {
        $band = $this->seedBeatles();
        $band = Band::find($band->id); // Convert the default Band to the specific Band class for this test.

        $joryResource = new TagJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $band->tags(), 'select `tags`.`name` from `tags` inner join `taggables` on `tags`.`id` = `taggables`.`tag_id` where `taggables`.`taggable_id` = ? and `taggables`.`taggable_type` = ?');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphToMany_relation_using_explicit_select()
    {
        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'tags_string']
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Band::query(), 'select `bands`.`name`, `bands`.`id` from `bands`');
    }

    /**
     * MORPHED BY MANY RELATIONS ===============================================================================================
     */

    /** @test */
    public function it_adds_the_foreign_key_field_when_requesting_a_morphedByMany_relation_using_explicit_select()
    {
        $joryResource = new TagJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
            'rlt' => [
                'bands' => [
                    'fld' => ['name'],
                ],
            ],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Tag::query(), 'select `tags`.`name`, `tags`.`id` from `tags`');
    }

    /** @test */
    public function it_adds_no_fields_on_the_relation_query_when_requesting_a_morphedByMany_relation_using_explicit_select(
    )
    {
        $tag = Tag::factory()->create([
            'name' => 'popular',
        ]);

        $joryResource = new BandJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name'],
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, $tag->bands(), 'select `bands`.`name` from `bands` inner join `taggables` on `bands`.`id` = `taggables`.`taggable_id` where `taggables`.`tag_id` = ? and `taggables`.`taggable_type` = ?');
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_morphedByMany_relation_using_explicit_select()
    {
        $joryResource = new TagJoryResource();
        $joryResource->setJory((new ArrayParser([
            'fld' => ['name', 'bands_string']
        ]))->getJory());

        $this->assertJoryResourceGeneratesQuery($joryResource, Tag::query(), 'select `tags`.`name`, `tags`.`id` from `tags`');
    }

    protected function assertJoryResourceGeneratesQuery(JoryResource $joryResource, $baseQuery, string $string)
    {
        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($baseQuery);

        $this->assertEquals($string, $baseQuery->toSql());
    }
}
