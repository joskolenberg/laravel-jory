<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Facade;

use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Http\Controllers\JoryController;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\Factories\UserFactory;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\TagJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\TagJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\TestCase;

class FacadeTest extends TestCase
{

    /** @test */
    public function it_can_apply_on_a_model_class_using_on()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $actual = Jory::on(User::class)
            ->applyArray([
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%b%',
                ]
            ])
            ->toArray();

        $this->assertEquals([
            ['name' => 'Bert'],
            ['name' => 'Big Bird'],
        ], $actual);
    }

    /** @test */
    public function it_can_apply_on_a_query_using_on()
    {
        $this->seedSesameStreet();
        Jory::register(UserJoryResource::class);

        $actual = Jory::on(User::query()->where('name', 'like', '%b%'))
            ->applyArray([
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%e%',
                ]
            ])
            ->toArray();

        $this->assertEquals([
            ['name' => 'Bert'],
        ], $actual);
    }

    /** @test */
    public function it_can_apply_on_a_model_instance_using_on()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);

        $actual = Jory::on($team)
            ->applyArray([
                'fld' => ['name', 'users.name'],
            ])
            ->toArray();

        $this->assertEquals([
            'name' => 'Sesame Street',
            'users' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird'],
                ['name' => 'Cookie Monster'],
                ['name' => 'Ernie'],
                ['name' => 'Oscar'],
                ['name' => 'The Count'],
            ]
        ], $actual);
    }

    /** @test */
    public function it_throws_an_exception_when_no_valid_resource_is_given_1()
    {
        $this->expectException(RegistrationNotFoundException::class);
        $this->expectExceptionMessage('No joryResource found for model ' . User::class . '. Does ' . User::class . ' have an associated JoryResource?');
        Jory::on(User::class);
    }

    /** @test */
    public function it_throws_an_exception_when_no_valid_resource_is_given_2()
    {
        $this->expectException(LaravelJoryException::class);
        $this->expectExceptionMessage('Unexpected type given. Please provide a model instance, Eloquent builder instance or a model\'s class name.');
        Jory::on(new UserFactory());
    }

    /** @test */
    public function it_can_register_a_jory_resource_by_class_name()
    {
        Jory::register(UserJoryResource::class);

        $this->assertInstanceOf(UserJoryResource::class, app(JoryResourcesRegister::class)->getByUri('user'));
    }

    /** @test */
    public function it_can_register_a_jory_resource_by_instance()
    {
        Jory::register(new UserJoryResource());

        $this->assertInstanceOf(UserJoryResource::class, app(JoryResourcesRegister::class)->getByUri('user'));
    }
}
