<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\Blueprint\Blueprint;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithBlueprint extends JoryBuilder
{
    protected function blueprint(Blueprint $blueprint): void
    {
        $blueprint->field('id');
        $blueprint->field('title')->description('The songs title.');
        $blueprint->field('album_id')->hideByDefault();
    }
}