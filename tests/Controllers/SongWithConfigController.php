<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfig;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfigTwo;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfigThree;

class SongWithConfigController extends Controller
{
    public function index(Request $request)
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfig::class);
        return (new SongJoryBuilderWithConfig())->onQuery(Song::query())->applyRequest($request);
    }

    public function indexTwo(Request $request)
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfigTwo::class);
        return (new SongJoryBuilderWithConfigTwo())->onQuery(Song::query())->applyRequest($request);
    }

    public function indexThree(Request $request)
    {
        JoryBuilder::register(Song::class, SongJoryBuilderWithConfigThree::class);
        return (new SongJoryBuilderWithConfigThree())->onQuery(Song::query())->applyRequest($request);
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
