<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\AlbumCoverJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResource;
use JosKolenberg\LaravelJory\Tests\Models\SubFolder\Album;
use JosKolenberg\LaravelJory\Tests\Scopes\BandNameSort;
use JosKolenberg\LaravelJory\Tests\Scopes\HasSongWithTitleFilter;
use JosKolenberg\LaravelJory\Tests\Scopes\NumberOfSongsFilter;
use JosKolenberg\LaravelJory\Tests\Scopes\NumberOfSongsSort;

class AlbumJoryResourceWithExplicitSelect extends JoryResource
{
    protected $modelClass = Album::class;

    protected function configure(): void
    {
        $this->explicitSelect();

        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('band_id')->filterable()->sortable();
        $this->field('release_date')->filterable()->sortable();
        $this->field('custom_field')->noSelect();

        $this->field('cover_image')->noSelect()->load('cover');
        $this->field('titles_string')->noSelect()->load('songs');
        $this->field('tag_names_string')->noSelect()->load('tags');

        $this->filter('number_of_songs', new NumberOfSongsFilter);
        $this->filter('has_song_with_title', new HasSongWithTitleFilter);
        $this->filter('album_cover.album_id');

        $this->sort('number_of_songs', new NumberOfSongsSort);
        $this->sort('band_name', new BandNameSort);

        $this->relation('songs');
        $this->relation('band');
        $this->relation('cover');
        $this->relation('albumCover', AlbumCoverJoryResource::class);
        $this->relation('customSongs2', CustomSongJoryResource::class);
        $this->relation('customSongs3', SongJoryResource::class);
        $this->relation('tags');
    }
}
