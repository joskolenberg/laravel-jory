<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\Models\Band;

class BandController extends Controller
{
    public function index()
    {
        return Jory::onModelClass(Band::class);
    }

    public function show($bandId)
    {
        return Jory::onModelClass(Band::class)->find($bandId);
    }

    public function firstByFilter()
    {
        return Jory::onModelClass(Band::class)->first();
    }

    public function count()
    {
        return Jory::onModelClass(Band::class)->count();
    }
}
