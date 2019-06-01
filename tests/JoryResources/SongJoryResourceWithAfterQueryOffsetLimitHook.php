<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithAfterQueryOffsetLimitHook extends JoryResource
{
    protected $modelClass = Song::class;

    /**
     * Configure the JoryBuilder.
     *
     */
    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();
        $this->field('album_id')->filterable()->sortable();
    }

    public function afterQueryBuild($query, $count = false): void
    {
        parent::afterQueryBuild($query);

        $query->offset(2)->limit(3);
    }
}
