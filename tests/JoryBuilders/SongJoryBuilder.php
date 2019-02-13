<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;

class SongJoryBuilder extends JoryBuilder
{
    /**
     * Configure the JoryBuilder.
     *
     * @param  \JosKolenberg\LaravelJory\Config\Config $config
     */
    protected function config(Config $config): void
    {
        // Fields
        $config->field('id')->filterable()->sortable();
        $config->field('title')->filterable()->sortable();
        $config->field('album_id')->filterable()->sortable();

        // Custom filters
        $config->filter('album_name');

        // Relations
        $config->relation('album');
    }
}
