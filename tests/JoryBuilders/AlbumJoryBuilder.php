<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use Illuminate\Database\Eloquent\Collection;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;

class AlbumJoryBuilder extends JoryBuilder
{
    protected function scopeNumberOfSongsFilter($query, $operator, $data)
    {
        $query->has('songs', $operator, $data);
    }

    protected function scopeHasSongWithTitleFilter($query, $operator, $data)
    {
        $query->whereHas('songs', function ($query) use ($operator, $data) {
            $query->where('title', $operator, $data);
        });
    }

    protected function scopeNumberOfSongsSort($query, string $order)
    {
        $query->withCount('songs')->orderBy('songs_count', $order);
    }

    protected function scopeBandNameSort($query, string $order)
    {
        $query->join('bands', 'band_id', 'bands.id')->orderBy('bands.name', $order);
    }

    protected function config(Config $config): void
    {
        $config->field('id')->filterable()->sortable();
        $config->field('name')->filterable()->sortable();
        $config->field('band_id')->filterable()->sortable();
        $config->field('release_date')->filterable()->sortable();
        $config->field('custom_field')->hideByDefault();

        $config->filter('number_of_songs');
        $config->filter('has_song_with_title');

        $config->sort('number_of_songs');
        $config->sort('band_name');

        $config->relation('songs');
        $config->relation('band');
        $config->relation('cover');
        $config->relation('album_cover');
        $config->relation('snake_case_album_cover');
    }

    protected function afterFetch(Collection $collection): Collection
    {
        if($this->hasField('custom_field')){
            $collection->each(function ($album){
                $album->custom_field = 'custom_value';
            });
        }

        return $collection;
    }
}
