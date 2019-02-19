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
        $config->field('custom_field')->hideByDefault();

        $config->filter('custom_filter_field');
        $config->sort('custom_sort_field');
    }

    protected function afterFetch(Collection $collection): Collection
    {
        if($this->hasField('custom_field')){
            $collection->each(function ($song){
                $song->title = 'altered';
                $song->custom_field = 'custom_value';
            });
        }

        if($this->hasFilter('custom_filter_field')){
            $collection->each(function ($song){
                $song->title = 'altered by filter';
            });
        }

        if($this->hasSort('custom_sort_field')){
            $collection->each(function ($song){
                $song->title = 'altered by sort';
            });
        }

        return $collection->filter(function ($model) {
            return $model->id > 100;
        });
    }

    protected function scopeCustomFilterFieldFilter($query, $operator, $data)
    {
        // Do nothing
    }

    protected function scopeCustomSortFieldSort($query, $order)
    {
        // Do nothing
    }
}
