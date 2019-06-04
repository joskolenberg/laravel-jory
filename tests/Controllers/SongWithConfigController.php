<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\JoryResources\SongJoryResourceWithConfig;
use JosKolenberg\LaravelJory\Tests\JoryResources\SongJoryResourceWithConfigThree;
use JosKolenberg\LaravelJory\Tests\JoryResources\SongJoryResourceWithConfigTwo;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongWithConfigController extends Controller
{
    public function index()
    {
        Jory::register(SongJoryResourceWithConfig::class);

        return Jory::onModelClass(Song::class);
    }

    public function indexTwo()
    {
        Jory::register(SongJoryResourceWithConfigTwo::class);

        return Jory::onModelClass(Song::class);
    }

    public function indexThree()
    {
        Jory::register(SongJoryResourceWithConfigThree::class);

        return Jory::onModelClass(Song::class);
    }

    public function options()
    {
        Jory::register(SongJoryResourceWithConfig::class);
        return response((new SongJoryResourceWithConfig())->getConfig()->toArray());
    }

    public function optionsTwo()
    {
        Jory::register(SongJoryResourceWithConfigTwo::class);
        return response((new SongJoryResourceWithConfigTwo())->getConfig()->toArray());
    }

    public function optionsThree()
    {
        Jory::register(SongJoryResourceWithConfigThree::class);
        return response((new SongJoryResourceWithConfigThree())->getConfig()->toArray());
    }
}
