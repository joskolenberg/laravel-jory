<?php

namespace JosKolenberg\LaravelJory\Tests\Traits;

use JosKolenberg\LaravelJory\GenericJoryBuilder;
use JosKolenberg\LaravelJory\Tests\Models\WithTraits\AlbumWithTrait;
use Orchestra\Testbench\TestCase;

class JoryTraitTest extends TestCase
{
    /** @test */
    public function it_can_give_a_genericJoryBuilder_when_applied()
    {
        $this->assertInstanceOf(GenericJoryBuilder::class, AlbumWithTrait::jory());
    }
}
