<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryBuilder;
use Illuminate\Database\Eloquent\Collection;

class SongJoryBuilderWithAfterFetchHook extends JoryBuilder
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

    protected function afterFetch(Collection $collection, Jory $jory): Collection
    {
        $collection = parent::afterFetch($collection, $jory);

        return $collection->filter(function ($model) {
            return $model->id > 100;
        });
    }
}
