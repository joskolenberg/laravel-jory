<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfig;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfigTwo;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithConfigThree;

class SongWithConfigController extends Controller
{
    public function index(Request $request)
    {
        return (new SongJoryBuilderWithConfig(Song::class))->onQuery(Song::query())->applyRequest($request);
    }

    public function indexTwo(Request $request)
    {
        return (new SongJoryBuilderWithConfigTwo(Song::class))->onQuery(Song::query())->applyRequest($request);
    }

    public function indexThree(Request $request)
    {
        return (new SongJoryBuilderWithConfigThree(Song::class))->onQuery(Song::query())->applyRequest($request);
    }

    public function options()
    {
        return (new SongJoryBuilderWithConfig(Song::class))->getConfig();
    }

    public function optionsTwo()
    {
        return (new SongJoryBuilderWithConfigTwo(Song::class))->getConfig();
    }

    public function optionsThree()
    {
        return (new SongJoryBuilderWithConfigThree(Song::class))->getConfig();
    }
}
