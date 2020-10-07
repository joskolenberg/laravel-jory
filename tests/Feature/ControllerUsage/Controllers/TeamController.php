<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ControllerUsage\Controllers;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Band;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;

class TeamController
{
    public function show($teamId)
    {
        return Jory::on(Team::class)->find($teamId);
    }
}
