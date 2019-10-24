<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered;

use Illuminate\Database\Eloquent\Collection;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Scopes\AlphabeticNameSort;
use JosKolenberg\LaravelJory\Tests\Scopes\BandNameSort;
use JosKolenberg\LaravelJory\Tests\Scopes\HasSmallIdFilter;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\CustomSongJoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Album;
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
        $this->field('custom_field')->hideByDefault();

        $this->field('cover_image')->load('cover')->hideByDefault();
        $this->field('titles_string')->load('songs')->hideByDefault();
        $this->field('tag_names_string')->load('tags')->hideByDefault();

        $this->filter('number_of_songs', new NumberOfSongsFilter);
        $this->filter('has_song_with_title', new HasSongWithTitleFilter);
        $this->filter('album_cover.album_id');
        $this->filter('has_small_id', new HasSmallIdFilter);

        $this->sort('number_of_songs', new NumberOfSongsSort);
        $this->sort('band_name', new BandNameSort);
        $this->sort('alphabetic_name', new AlphabeticNameSort);

        $this->relation('songs', SongJoryResource::class);
        $this->relation('band');
        $this->relation('cover');
        $this->relation('album_cover', AlbumCoverJoryResource::class);
        $this->relation('snake_case_album_cover');
        $this->relation('custom_songs_2', CustomSongJoryResource::class);
        $this->relation('custom_songs_3', SongJoryResource::class);
        $this->relation('tags');
    }
}
