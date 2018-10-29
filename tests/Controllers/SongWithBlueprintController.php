<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithBlueprint;

class SongWithBlueprintController extends Controller
{
    public function index(Request $request)
    {
        return (new SongJoryBuilderWithBlueprint())->onQuery(Song::query())->applyRequest($request);
    }

    public function options()
    {
        return (new SongJoryBuilderWithBlueprint())->getBlueprint();
    }
}