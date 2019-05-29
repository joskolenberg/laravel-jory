<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use Illuminate\Database\Eloquent\Collection;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Album;
use JosKolenberg\LaravelJory\Tests\Models\AlbumCover;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class AlbumJoryResource extends JoryResource
{
    protected $modelClass = Album::class;

    public function scopeNumberOfSongsFilter($query, $operator, $data)
    {
        $query->has('songs', $operator, $data);
    }

    public function scopeHasSongWithTitleFilter($query, $operator, $data)
    {
        $query->whereHas('songs', function ($query) use ($operator, $data) {
            $query->where('title', $operator, $data);
        });
    }

    public function scopeNumberOfSongsSort($query, string $order)
    {
        $query->withCount('songs')->orderBy('songs_count', $order);
    }

    public function scopeBandNameSort($query, string $order)
    {
        $query->join('bands', 'band_id', 'bands.id')->orderBy('bands.name', $order);
    }

    protected function configure(): void
    {
        $this->field('id')->filterable()->sortable();
        $this->field('name')->filterable()->sortable();
        $this->field('band_id')->filterable()->sortable();
        $this->field('release_date')->filterable()->sortable();
        $this->field('custom_field')->hideByDefault();

        $this->filter('number_of_songs');
        $this->filter('has_song_with_title');

        $this->sort('number_of_songs');
        $this->sort('band_name');

        $this->relation('songs', Song::class);
        $this->relation('band');
        $this->relation('cover', AlbumCover::class);
        $this->relation('album_cover', AlbumCover::class);
        $this->relation('snake_case_album_cover');
    }

    public function afterFetch(Collection $collection): Collection
    {
        if($this->hasField('custom_field')){
            $collection->each(function ($album){
                $album->custom_field = 'custom value';
            });
        }

        return $collection;
    }
}
