<?php


namespace JosKolenberg\LaravelJory\Tests\Scopes;


use JosKolenberg\LaravelJory\Scopes\SortScope;

class AlbumCoverAlbumNameSort implements SortScope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param string $order
     * @return void
     */
    public function apply($builder, string $order = 'asc'): void
    {
        $builder->join('albums', 'album_covers.album_id', 'albums.id')->orderBy('albums.name', $order);
    }
}