<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithBeforeQueryBuildOffsetLimitHook extends JoryBuilder
{
    protected function beforeQueryBuild($query, Jory $jory, $count = false)
    {
        parent::beforeQueryBuild($query, $jory);

        $query->offset(2)->limit(3);
    }
}