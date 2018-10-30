<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Blueprint\Blueprint;

class SongJoryBuilderWithBlueprintThree extends JoryBuilder
{
    protected function blueprint(Blueprint $blueprint): void
    {
        $blueprint->limitDefault(null)->limitMax(10);

        $blueprint->sort('title')->default(2, 'desc');
        $blueprint->sort('album_name')->default(1, 'asc');
    }
}