<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\CustomSongJoryResource;
use JosKolenberg\LaravelJory\Tests\Models\SubFolder\Album;
use JosKolenberg\LaravelJory\Tests\Scopes\AlphabeticNameSort;
use JosKolenberg\LaravelJory\Tests\Scopes\BandNameSort;
use JosKolenberg\LaravelJory\Tests\Scopes\HasSmallIdFilter;
use JosKolenberg\LaravelJory\Tests\Scopes\HasSongWithTitleFilter;
use JosKolenberg\LaravelJory\Tests\Scopes\NumberOfSongsFilter;
use JosKolenberg\LaravelJory\Tests\Scopes\NumberOfSongsSort;

class AlbumJoryResource extends JoryResource
{
    protected $modelClass = Album::class;

    protected function configure(): void
    {
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('band_id')->filterable()->sortable();
        $this->field('release_date')->filterable()->sortable();
        $this->field('custom_field');

        $this->field('cover_image')->load('cover');
        $this->field('titles_string')->load('songs');
        $this->field('tag_names_string')->load('tags');

        $this->filter('number_of_songs', new NumberOfSongsFilter);
        $this->filter('has_song_with_title', new HasSongWithTitleFilter);
        $this->filter('albumCover.album_id');
        $this->filter('has_small_id', new HasSmallIdFilter);

        $this->sort('number_of_songs', new NumberOfSongsSort);
        $this->sort('band_name', new BandNameSort);
        $this->sort('alphabetic_name', new AlphabeticNameSort);

        $this->relation('songs', SongJoryResource::class);
        $this->relation('band');
        $this->relation('cover');
        $this->relation('albumCover', AlbumCoverJoryResource::class);
        $this->relation('customSongs2', CustomSongJoryResource::class);
        $this->relation('customSongs3', SongJoryResource::class);
        $this->relation('tags');
    }
}
