<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered;

use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithAfterQueryBuildFilterHook extends JoryResource
{
    protected $modelClass = Song::class;

    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();
        $this->field('album_id')->filterable()->sortable();
        $this->field('custom_field');
    }

    public function afterQueryBuild($query, $count = false): void
    {
        parent::afterQueryBuild($query);

        $query->where('title', 'like', '%love%');
    }
}
