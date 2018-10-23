<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Tests\Models\Band;

class BandController extends Controller
{
    public function index(Request $request)
    {
        return Band::jory()->applyRequest($request);
    }

    public function show($bandId, Request $request)
    {
        $band = Band::findOrFail($bandId);

        return Band::jory()->applyRequest($request)->onModel($band);
    }

    public function firstByFilter(Request $request)
    {
        return Band::jory()->applyRequest($request)->first();
    }
}
