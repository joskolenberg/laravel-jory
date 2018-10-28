<?php

namespace JosKolenberg\LaravelJory\Tests\JoryBuilders;

use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\JoryBuilder;

class SongJoryBuilderWithBeforeQueryBuildOffsetLimitHook extends JoryBuilder
{
    protected function beforeQueryBuild($query, Jory $jory)
    {
        $query->offset(2)->limit(3);

        parent::beforeQueryBuild($query, $jory);
    }
}