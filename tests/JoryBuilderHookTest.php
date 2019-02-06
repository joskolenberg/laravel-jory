<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithAfterFetchHook;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithAfterQuerySortHook;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithBeforeQueryBuildSortHook;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithAfterQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithAfterQueryOffsetLimitHook;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithBeforeQueryBuildFilterHook;

class JoryBuilderHookTest extends TestCase
{
    /** @test */
    public function it_can_hook_into_the_query_before_query_build_with_a_filter_1()
    {
        $builder = new SongJoryBuilderWithBeforeQueryBuildFilterHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 3,
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => 'And the Gods Made Love',
            ],
            [
                'title' => 'Bold as Love',
            ],
            [
                'title' => 'Little Miss Lover',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_hook_into_the_query_before_query_build_with_a_filter_2()
    {
        $builder = new SongJoryBuilderWithBeforeQueryBuildFilterHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'flt' => [
                'f' => 'title',
                'o' => 'like',
                'd' => '%and%',
            ],
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 3,
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => 'And the Gods Made Love',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_hook_into_the_query_before_query_build_with_a_sort_1()
    {
        $builder = new SongJoryBuilderWithBeforeQueryBuildSortHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 5,
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => '1983... (A Merman I Should Turn to Be)',
            ],
            [
                'title' => 'All Along the Watchtower',
            ],
            [
                'title' => 'And the Gods Made Love',
            ],
            [
                'title' => 'Burning of the Midnight Lamp',
            ],
            [
                'title' => 'Come On (Part I)',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_hook_into_the_query_after_query_build_with_a_filter_1()
    {
        $builder = new SongJoryBuilderWithAfterQueryBuildFilterHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 3,
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => 'And the Gods Made Love',
            ],
            [
                'title' => 'Bold as Love',
            ],
            [
                'title' => 'Little Miss Lover',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_hook_into_the_query_after_query_build_with_a_filter_2()
    {
        $builder = new SongJoryBuilderWithAfterQueryBuildFilterHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'flt' => [
                'f' => 'title',
                'o' => 'like',
                'd' => '%and%',
            ],
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 3,
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => 'And the Gods Made Love',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_hook_into_the_query_after_query_build_with_offset_and_limit_1()
    {
        $builder = new SongJoryBuilderWithAfterQueryOffsetLimitHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'flt' => [
                'f' => 'title',
                'o' => 'like',
                'd' => '%love%',
            ],
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'ofs' => 0,
            'lmt' => 4,
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => 'Little Miss Lover',
            ],
            [
                'title' => 'Love In Vain (Robert Johnson)',
            ],
            [
                'title' => 'Love or Confusion',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_hook_into_the_query_after_query_build_with_a_sort_1()
    {
        $builder = new SongJoryBuilderWithAfterQuerySortHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'fld' => [
                'title',
            ],
            'lmt' => 5,
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => 'Your Time Is Gonna Come',
            ],
            [
                'title' => 'You Shook Me',
            ],
            [
                'title' => 'You Never Give Me Your Money',
            ],
            [
                'title' => 'You Gotta Move',
            ],
            [
                'title' => 'You Got The Silver',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_hook_into_the_query_after_query_build_with_a_sort_2()
    {
        $builder = new SongJoryBuilderWithAfterQuerySortHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'srt' => [
                'album_id',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 5,
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => 'You Got The Silver',
            ],
            [
                'title' => 'You Can\'t Always Get What You Want',
            ],
            [
                'title' => 'Monkey Man',
            ],
            [
                'title' => 'Midnight Rambler',
            ],
            [
                'title' => 'Love In Vain (Robert Johnson)',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_hook_into_the_after_fetch_on_the_collection()
    {
        $builder = new SongJoryBuilderWithAfterFetchHook(Song::class);
        $builder->onQuery(Song::query());
        $builder->applyArray([
            'srt' => [
                '-album_id',
                'title',
            ],
            'flt' => [
                'f' => 'title',
                'o' => 'like',
                'd' => '%love%',
            ],
            'fld' => [
                'title',
            ],
        ]);
        $result = $builder->toArray();

        $this->assertEquals([
            [
                'title' => 'And the Gods Made Love',
            ],
            [
                'title' => 'Bold as Love',
            ],
            [
                'title' => 'Little Miss Lover',
            ],
            [
                'title' => 'Love or Confusion',
            ],
            [
                'title' => 'May This Be Love',
            ],
        ], $result);

        $this->assertQueryCount(1);
    }
}
