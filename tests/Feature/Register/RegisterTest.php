<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Register;

use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class RegisterTest extends TestCase
{

    /** @test */
    public function it_throws_an_exception_when_no_associated_jory_resource_is_found_when_the_relation_is_requested()
    {
        Jory::register(TeamJoryResource::class);
        $team = $this->seedSesameStreet();

        $this->expectException(RegistrationNotFoundException::class);
        $this->expectExceptionMessage('No joryResource found for model ' . User::class . '. Does ' . User::class . ' have an associated JoryResource?');

        Jory::on($team)->apply([
            'rlt' => [
                'users' => []
            ]
        ])->toArray();
    }

    /** @test */
    public function it_doesnt_throw_an_exception_when_no_associated_jory_resource_is_found_as_long_as_the_relation_isnt_requested()
    {
        Jory::register(TeamJoryResource::class);
        $team = $this->seedSesameStreet();

        Jory::on($team)->apply([
            'fld' => 'name'
        ])->toArray();

        $this->assertTrue(true);
    }
}
