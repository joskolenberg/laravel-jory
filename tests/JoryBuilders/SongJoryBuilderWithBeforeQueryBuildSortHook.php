<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithBeforeQueryBuildSortHook extends JoryBuilder
{
    protected function beforeQueryBuild($query, Jory $jory, $count = false)
    {
        parent::beforeQueryBuild($query, $jory);

        $query->orderBy('album_id', 'desc');
    }
}