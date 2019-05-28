<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;

class PersonJoryBuilder extends JoryBuilder
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
        $config->field('first_name')->filterable()->sortable();
        $config->field('last_name')->filterable()->sortable();
        $config->field('date_of_birth')->filterable()->sortable();
        $config->field('full_name')->filterable();

        $config->filter('band.albums.songs.title');
        $config->filter('instruments.name');

        // Relations
        $config->relation('instruments');
        $config->relation('groupies');
    }

    protected function scopeFullNameFilter($query, $operator, $data)
    {
        $query->where('first_name', 'like', '%'.$data.'%');
        $query->orWhere('last_name', 'like', '%'.$data.'%');
    }
}
