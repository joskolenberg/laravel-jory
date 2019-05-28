<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfig;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfigThree;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfigTwo;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongWithConfigController extends Controller
{
    public function index()
    {
        Jory::register(Song::class, SongJoryBuilderWithConfig::class);

        return Jory::onModelClass(Song::class);
    }

    public function indexTwo()
    {
        Jory::register(Song::class, SongJoryBuilderWithConfigTwo::class);

        return Jory::onModelClass(Song::class);
    }

    public function indexThree()
    {
        Jory::register(Song::class, SongJoryBuilderWithConfigThree::class);

        return Jory::onModelClass(Song::class);
    }

    public function options()
    {
        Jory::register(Song::class, SongJoryBuilderWithConfig::class);
        return (new SongJoryBuilderWithConfig())->getConfig();
    }

    public function optionsTwo()
    {
        Jory::register(Song::class, SongJoryBuilderWithConfigTwo::class);
        return (new SongJoryBuilderWithConfigTwo())->getConfig();
    }

    public function optionsThree()
    {
        Jory::register(Song::class, SongJoryBuilderWithConfigThree::class);
        return (new SongJoryBuilderWithConfigThree())->getConfig();
    }
}
