<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAfterFetchHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAfterQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAfterQueryOffsetLimitHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithAfterQuerySortHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithBeforeQueryBuildFilterHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithBeforeQueryBuildSortHook;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\TagJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\TagJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class RegisterTest extends TestCase
{
    /** @test */
    public function it_gives_manually_added_jory_resources_precedence_over_autoregistered_jory_resources_with_the_same_uri()
    {
        $register = app(JoryResourcesRegister::class);
        $this->assertInstanceOf(TagJoryResource::class, $register->getByUri('tag'));

        Jory::register(TagJoryResourceWithExplicitSelect::class);

        $this->assertInstanceOf(TagJoryResourceWithExplicitSelect::class, $register->getByUri('tag'));
    }

    /** @test */
    public function it_gives_any_newly_added_jory_resources_precedence_over_earlier_registered_jory_resources_with_the_same_uri()
    {
        $register = app(JoryResourcesRegister::class);

        $this->assertInstanceOf(SongJoryResource::class, $register->getByUri('song'));

        Jory::register(SongJoryResourceWithAfterQueryBuildFilterHook::class);
        $this->assertInstanceOf(SongJoryResourceWithAfterQueryBuildFilterHook::class, $register->getByUri('song'));

        Jory::register(SongJoryResourceWithAfterQueryOffsetLimitHook::class);
        $this->assertInstanceOf(SongJoryResourceWithAfterQueryOffsetLimitHook::class, $register->getByUri('song'));
    }
}
