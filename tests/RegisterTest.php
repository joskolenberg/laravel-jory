<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResourceWithAfterFetchHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResourceWithAfterQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResourceWithAfterQueryOffsetLimitHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResourceWithAfterQuerySortHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResourceWithBeforeQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResourceWithBeforeQueryBuildSortHook;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class RegisterTest extends TestCase
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

}
