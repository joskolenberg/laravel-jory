<?php

namespace App\Http\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;

class BandJoryBuilder extends JoryBuilder
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
        $config->field('name')->filterable()->sortable();
        $config->field('year_start')->filterable()->sortable();
        $config->field('year_end')->filterable()->sortable();
        $config->field('all_albums_string')->hideByDefault();

        // Relations
        $config->relation('people');
        $config->relation('albums');
        $config->relation('songs');
    }
}
