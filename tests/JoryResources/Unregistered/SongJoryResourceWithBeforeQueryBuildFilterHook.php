<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithBeforeQueryBuildFilterHook extends JoryResource
{
    protected $modelClass = Song::class;

    protected $uri = 'song-custom';

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();
        $this->field('album_id')->filterable()->sortable();
    }

    public function beforeQueryBuild($query, $count = false): void
    {
        parent::beforeQueryBuild($query);

        $query->where('title', 'like', '%love%');
    }
}
