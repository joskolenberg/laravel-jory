<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Blueprint\Blueprint;

class SongJoryBuilderWithBlueprint extends JoryBuilder
{
    protected function blueprint(Blueprint $blueprint): void
    {
        $blueprint->field('id');
        $blueprint->field('title')->description('The songs title.');
        $blueprint->field('album_id')->hideByDefault();

        $blueprint->filter('title')->description('Filter on the title.');
        $blueprint->filter('album_id')->description('Filter on the album id.')->operators(['=']);
    }
}