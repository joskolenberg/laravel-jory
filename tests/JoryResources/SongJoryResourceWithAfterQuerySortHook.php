<?php

namespace JosKolenberg\LaravelJory\Tests\JoryResources;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class SongJoryResourceWithAfterQuerySortHook extends JoryResource
{
    protected $modelClass = Song::class;

    /**
     * Configure the JoryBuilder.
     *
     * @param  \JosKolenberg\LaravelJory\Config\Config $config
     */
    protected function configure(): void
    {
        // Fields
        $this->field('id')->filterable()->sortable();
        $this->field('title')->filterable()->sortable();
        $this->field('album_id')->filterable()->sortable();
    }

    public function afterQueryBuild($query, Jory $jory, $count = false): void
    {
        parent::afterQueryBuild($query, $jory);

        $query->orderBy('title', 'desc');
    }
}
