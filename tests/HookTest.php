<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAfterFetchHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAfterQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAfterQueryOffsetLimitHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAfterQuerySortHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithBeforeQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithBeforeQueryBuildSortHook;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class HookTest extends TestCase
{
    /** @test */
    public function it_can_hook_into_the_query_before_query_build_with_a_filter_1()
    {
        Jory::register(SongJoryResourceWithBeforeQueryBuildFilterHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 3,
        ])->toArray();

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
        Jory::register(SongJoryResourceWithBeforeQueryBuildFilterHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
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
        ])->toArray();

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
        Jory::register(SongJoryResourceWithBeforeQueryBuildSortHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 5,
        ])->toArray();

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
        Jory::register(SongJoryResourceWithAfterQueryBuildFilterHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'srt' => [
                'title',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 3,
        ])->toArray();

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
        Jory::register(SongJoryResourceWithAfterQueryBuildFilterHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
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
        ])->toArray();

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
        Jory::register(SongJoryResourceWithAfterQueryOffsetLimitHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
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
        ])->toArray();

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
        Jory::register(SongJoryResourceWithAfterQuerySortHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'fld' => [
                'title',
            ],
            'lmt' => 5,
        ])->toArray();

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
        Jory::register(SongJoryResourceWithAfterQuerySortHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'srt' => [
                'album_id',
            ],
            'fld' => [
                'title',
            ],
            'lmt' => 5,
        ])->toArray();

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
        Jory::register(SongJoryResourceWithAfterFetchHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
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
        ])->toArray();

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

    /** @test */
    public function it_can_check_if_a_field_is_requested()
    {
        Jory::register(SongJoryResourceWithAfterFetchHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'fld' => [
                'title',
                'custom_field',
            ],
            'srt' => [
                '-id',
            ],
            'lmt' => 2,
        ])->toArray();

        $this->assertEquals([
            [
                'title' => 'altered',
                'custom_field' => 'custom_value',
            ],
            [
                'title' => 'altered',
                'custom_field' => 'custom_value',
            ],
        ], $result);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'fld' => [
                'title',
            ],
            'srt' => [
                '-id',
            ],
            'lmt' => 2,
        ])->toArray();

        $this->assertEquals([
            [
                'title' => 'Voodoo Child (Slight Return)',
            ],
            [
                'title' => 'All Along the Watchtower',
            ],
        ], $result);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_check_if_a_filter_on_a_field_will_be_applied()
    {
        Jory::register(SongJoryResourceWithAfterFetchHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'flt' => [
                'f' => 'custom_filter_field',
            ],
            'srt' => [
                '-id',
            ],
            'lmt' => 2,
        ])->toArray();

        $this->assertEquals([
            [
                'id' => 147,
                'album_id' => 12,
                'title' => 'altered by filter',
            ],
            [
                'id' => 146,
                'album_id' => 12,
                'title' => 'altered by filter',
            ],
        ], $result);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'srt' => [
                '-id',
            ],
            'lmt' => 2,
        ])->toArray();

        $this->assertEquals([
            [
                'id' => 147,
                'album_id' => 12,
                'title' => 'Voodoo Child (Slight Return)',
            ],
            [
                'id' => 146,
                'album_id' => 12,
                'title' => 'All Along the Watchtower',
            ],
        ], $result);

        $this->assertQueryCount(2);
    }

    /** @test */
    public function it_can_check_if_a_sort_on_a_field_will_be_applied()
    {
        Jory::register(SongJoryResourceWithAfterFetchHook::class);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'srt' => [
                '-id',
                '-custom_sort_field',
            ],
            'lmt' => 2,
        ])->toArray();

        $this->assertEquals([
            [
                'id' => 147,
                'album_id' => 12,
                'title' => 'altered by sort',
            ],
            [
                'id' => 146,
                'album_id' => 12,
                'title' => 'altered by sort',
            ],
        ], $result);

        $result = Jory::onModelClass(Song::class)->applyArray([
            'srt' => [
                '-id',
            ],
            'lmt' => 2,
        ])->toArray();

        $this->assertEquals([
            [
                'id' => 147,
                'album_id' => 12,
                'title' => 'Voodoo Child (Slight Return)',
            ],
            [
                'id' => 146,
                'album_id' => 12,
                'title' => 'All Along the Watchtower',
            ],
        ], $result);

        $this->assertQueryCount(2);
    }
}
