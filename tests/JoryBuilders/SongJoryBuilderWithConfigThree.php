<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;

class SongJoryBuilderWithConfigThree extends JoryBuilder
{
    protected function config(Config $config): void
    {
        $config->limitDefault(null)->limitMax(10);

        $config->sort('title')->default(2, 'desc');
        $config->sort('album_name')->default(1, 'asc');
    }
}