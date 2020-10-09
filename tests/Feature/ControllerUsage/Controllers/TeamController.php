<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ControllerUsage\Controllers;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;

class TeamController
{
    public function show($teamId)
    {
        return Jory::on(Team::class)->find($teamId);
    }
}
