<?php

namespace JosKolenberg\LaravelJory\Tests\Controllers;

use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\Models\Band;

class BandController extends Controller
{
    public function index()
    {
        return Jory::on(Band::class);
    }

    public function show($bandId)
    {
        return Jory::on(Band::class)->find($bandId);
    }

    public function firstByFilter()
    {
        return Jory::on(Band::query())->first();
    }

    public function count()
    {
        return Jory::on(Band::query())->count();
    }
}
