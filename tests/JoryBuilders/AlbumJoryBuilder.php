<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Blueprint\Blueprint;
use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;

class AlbumJoryBuilder extends JoryBuilder
{
    protected function scopeNumberOfSongsFilter($query, $operator, $value)
    {
        $query->has('songs', $operator, $value);
    }

    protected function scopeHasSongWithTitleFilter($query, $operator, $value)
    {
        $query->whereHas('songs', function ($query) use ($operator, $value) {
            $query->where('title', $operator, $value);
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

    protected function blueprint(Blueprint $blueprint): void
    {
        parent::blueprint($blueprint);

        $blueprint->field('id');
        $blueprint->field('name');
        $blueprint->field('band_id');
        $blueprint->field('release_date');

        $blueprint->filter('id');
        $blueprint->filter('name');
        $blueprint->filter('band_id');
        $blueprint->filter('release_date');
        $blueprint->filter('number_of_songs');
        $blueprint->filter('has_song_with_title');

        $blueprint->sort('id');
        $blueprint->sort('name');
        $blueprint->sort('band_id');
        $blueprint->sort('release_date');
        $blueprint->sort('number_of_songs');
        $blueprint->sort('band_name');

        $blueprint->relation('songs', Song::class);
        $blueprint->relation('band', Band::class);
        $blueprint->relation('cover', AlbumCover::class);
        $blueprint->relation('album_cover', AlbumCover::class);
    }
}
