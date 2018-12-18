<?php

namespace JosKolenberg\LaravelJory\Tests\Models;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\SongJoryBuilderWithBeforeQueryBuildFilterHook;

class SongWithCustomJoryBuilder extends Song
{

    public static function getJoryBuilder(): JoryBuilder
    {
        return new SongJoryBuilderWithBeforeQueryBuildFilterHook();
    }
}
