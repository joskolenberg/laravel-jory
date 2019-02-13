<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithBeforeQueryBuildSortHook extends JoryBuilder
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
    }

    protected function beforeQueryBuild($query, Jory $jory, $count = false): void
    {
        parent::beforeQueryBuild($query, $jory);

        $query->orderBy('album_id', 'desc');
    }
}
