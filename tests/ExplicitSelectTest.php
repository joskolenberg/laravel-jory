<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\AlbumCoverJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\AlbumJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\PersonJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;
use JosKolenberg\LaravelJory\Tests\Models\Person;

class ExplicitSelectTest extends TestCase
{

    /** @test */
    public function it_only_selects_requested_fields_when_using_explicit_select()
    {
        $query = Person::query();

        $joryResource = new PersonJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['full_name']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `first_name`, `last_name` from `people`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_fields_using_explicit_select()
    {
        $jory = [
            'fld' => ['full_name', 'date_of_birth']
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
                'cover' => []
            ]
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
            'fld' => ['image']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `album_covers`.`image`, `album_covers`.`album_id` from `album_covers` where `album_covers`.`album_id` = ? and `album_covers`.`album_id` is not null', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_hasOne_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name'],
            'rlt' => [
                'cover' => [
                    'fld' => ['image']
                ]
            ]
        ];

        $expected = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        Jory::register(AlbumCoverJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

    /** @test */
    public function it_adds_the_primary_key_field_when_requesting_a_field_which_eager_loads_a_hasOne_relation_using_explicit_select()
    {
        $query = Album::query();

        $joryResource = new AlbumJoryResourceWithExplicitSelect();

        $joryResource->setJory((new ArrayParser([
            'fld' => ['cover_image']
        ]))->getJory());

        $joryBuilder = new JoryBuilder($joryResource);

        $joryBuilder->applyOnQuery($query);

        $this->assertEquals('select `albums`.`id` from `albums`', $query->toSql());
    }

    /** @test */
    public function it_returns_the_same_result_when_requesting_a_field_which_eager_loads_a_hasOne_relation_using_explicit_select()
    {
        $jory = [
            'fld' => ['name', 'cover_image'],
        ];

        $expected = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();
        Jory::register(AlbumJoryResourceWithExplicitSelect::class);
        Jory::register(AlbumCoverJoryResourceWithExplicitSelect::class);
        $actual = $this->json('GET', 'jory/album', ['jory' => $jory])->getContent();

        $this->assertEquals($expected, $actual);

        $this->assertQueryCount(4);
    }

}

//'hasOne', ok
//            'belongsTo',
//            'hasMany',
//            'belongsToMany',
//            'hasManyThrough',
//            'hasOneThrough',
//            'morphOne',
//            'morphMany',
//            'morphToMany',
//            'morphedByMany',

// In ieder gaval 1 veld selecten als er niets is geselecteerd