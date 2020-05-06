<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Http\Controllers\JoryController;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\TagJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\TagJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\Models\Song;

class FacadeTest extends TestCase
{

    /** @test */
    public function it_can_apply_on_a_model_class_using_on()
    {
        $actual = Jory::on(Song::class)
            ->applyArray([
                'fld' => 'title',
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love',
                ]
            ])
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'May This Be Love'],
            ['title' => 'Bold as Love'],
            ['title' => 'And the Gods Made Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_on_a_query_using_on()
    {
        $actual = Jory::on(Song::query()->where('title', 'like', '%ol%'))
            ->applyArray([
                'fld' => 'title',
                'flt' => [
                    'f' => 'title',
                    'o' => 'like',
                    'd' => '%love',
                ]
            ])
            ->toArray();

        $this->assertEquals([
            ['title' => 'Whole Lotta Love'],
            ['title' => 'Bold as Love'],
        ], $actual);

        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_apply_on_a_model_instance_using_on()
    {
        $actual = Jory::on(Song::find(47))
            ->applyArray([
                'fld' => 'title',
                'rlt' => [
                    'album' => [
                        'fld' => 'name',
                    ]
                ]
            ])
            ->toArray();

        $this->assertEquals([
            'title' => 'Whole Lotta Love',
            'album' => [
                'name' => 'Led Zeppelin II',
            ]
        ], $actual);

        $this->assertQueryCount(3);
    }

    /** @test */
    public function it_throws_an_exception_when_no_valid_resource_is_given_1()
    {
        $this->expectException(RegistrationNotFoundException::class);
        $this->expectExceptionMessage('No joryResource found for model JosKolenberg\LaravelJory\Http\Controllers\JoryController. Does JosKolenberg\LaravelJory\Http\Controllers\JoryController have an associated JoryResource?');
        Jory::on(JoryController::class);
    }

    /** @test */
    public function it_throws_an_exception_when_no_valid_resource_is_given_2()
    {
        $this->expectException(LaravelJoryException::class);
        $this->expectExceptionMessage('Unexpected type given. Please provide a model instance, Eloquent builder instance or a model\'s class name.');
        Jory::on(new JoryController());
    }

    /** @test */
    public function it_can_register_a_jory_resource_by_class_name()
    {
        $this->assertInstanceOf(TagJoryResource::class, app(JoryResourcesRegister::class)->getByUri('tag'));

        Jory::register(TagJoryResourceWithExplicitSelect::class);

        $this->assertInstanceOf(TagJoryResourceWithExplicitSelect::class, app(JoryResourcesRegister::class)->getByUri('tag'));
    }

    /** @test */
    public function it_can_register_a_jory_resource_by_instance()
    {
        $this->assertInstanceOf(TagJoryResource::class, app(JoryResourcesRegister::class)->getByUri('tag'));

        Jory::register(new TagJoryResourceWithExplicitSelect());

        $this->assertInstanceOf(TagJoryResourceWithExplicitSelect::class, app(JoryResourcesRegister::class)->getByUri('tag'));
    }
}
