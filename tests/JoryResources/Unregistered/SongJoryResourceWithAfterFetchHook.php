<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use Illuminate\Database\Eloquent\Collection;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\Scopes\CustomFilterFieldFilter;

class SongJoryResourceWithAfterFetchHook extends JoryResource
{
    protected $modelClass = Song::class;

    protected $uri = 'song-with-after-fetch';

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();
        $this->field('album_id')->filterable()->sortable();
        $this->field('custom_field')->hideByDefault();

        $this->filter('custom_filter_field', new CustomFilterFieldFilter);
        $this->sort('custom_sort_field');
    }

    public function afterFetch(Collection $collection): Collection
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

    public function scopeCustomSortFieldSort($query, $order)
    {
        // Do nothing
    }
}
