<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\Models\Band;

class BandController extends Controller
{
    public function index()
    {
        return Jory::byModel(Band::class);
    }

    public function show($bandId)
    {
        return Jory::byModel(Band::class)->find($bandId);
    }

    public function firstByFilter()
    {
        return Jory::byModel(Band::class)->first();
    }

    public function count()
    {
        return Jory::byModel(Band::class)->count();
    }
}
