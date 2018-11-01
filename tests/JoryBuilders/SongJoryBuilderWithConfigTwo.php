<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;

class SongJoryBuilderWithConfigTwo extends JoryBuilder
{
    protected function config(Config $config): void
    {
        $config->limitDefault(null)->limitMax(null);
    }
}