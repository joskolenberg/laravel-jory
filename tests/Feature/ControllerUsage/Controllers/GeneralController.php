<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\ControllerUsage\Controllers;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultModels\Team;

class GeneralController
{
    public function multiple()
    {
        return Jory::multiple();
    }
}
