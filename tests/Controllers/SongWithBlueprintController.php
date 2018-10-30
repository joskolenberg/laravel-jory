<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithBlueprint;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithBlueprintTwo;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithBlueprintThree;

class SongWithBlueprintController extends Controller
{
    public function index(Request $request)
    {
        return (new SongJoryBuilderWithBlueprint())->onQuery(Song::query())->applyRequest($request);
    }

    public function indexTwo(Request $request)
    {
        return (new SongJoryBuilderWithBlueprintTwo())->onQuery(Song::query())->applyRequest($request);
    }

    public function indexThree(Request $request)
    {
        return (new SongJoryBuilderWithBlueprintThree())->onQuery(Song::query())->applyRequest($request);
    }

    public function options()
    {
        return (new SongJoryBuilderWithBlueprint())->getBlueprint();
    }

    public function optionsTwo()
    {
        return (new SongJoryBuilderWithBlueprintTwo())->getBlueprint();
    }

    public function optionsThree()
    {
        return (new SongJoryBuilderWithBlueprintThree())->getBlueprint();
    }
}