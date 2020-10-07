<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ControllerUsage\Controllers;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Band;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class UserController
{
    public function index()
    {
        return Jory::on(User::class);
    }
//
//    public function show($bandId)
//    {
//        return Jory::on(Band::class)->find($bandId);
//    }

    public function firstByFilter()
    {
        return Jory::on(User::query())->first();
    }

    public function count()
    {
        return Jory::on(User::query())->count();
    }
}
