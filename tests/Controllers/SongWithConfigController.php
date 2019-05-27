<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfig;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfigThree;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfigTwo;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongWithConfigController extends Controller
{
    public function index()
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfig::class);

        return Jory::byModel(Song::class);
    }

    public function indexTwo()
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfigTwo::class);

        return Jory::byModel(Song::class);
    }

    public function indexThree()
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfigThree::class);

        return Jory::byModel(Song::class);
    }

    public function options()
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfig::class);
        return (new SongJoryBuilderWithConfig())->getConfig();
    }

    public function optionsTwo()
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfigTwo::class);
        return (new SongJoryBuilderWithConfigTwo())->getConfig();
    }

    public function optionsThree()
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfigThree::class);
        return (new SongJoryBuilderWithConfigThree())->getConfig();
    }
}
