<?php

namespace JosKolenberg\LaravelJory\Tests;

use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;
use JosKolenberg\LaravelJory\Tests\JoryBuilders\GrrrroupieJoryBuilder;
use JosKolenberg\LaravelJory\Tests\Models\Groupie;

class JoryRegisterTest extends TestCase
{
    /** @test */
    public function it_throws_an_exception_when_no_associated_jorybuilder_is_found()
    {
        $response = $this->json('GET', 'jory/person/1', [
            'jory' => '{"rlt":{"groupies":{}}}',
        ]);

        $response->assertStatus(500);

        $this->expectException(RegistrationNotFoundException::class);
        $this->expectExceptionMessage('No registration found for model JosKolenberg\LaravelJory\Tests\Models\Groupie. Does JosKolenberg\LaravelJory\Tests\Models\Groupie have an associated JoryBuilder?');

        $register = app(JoryBuildersRegister::class);

        $register->getByModelClass(Groupie::class);
    }

    /** @test */
    public function it_throws_an_exception_when_no_assiociated_model_is_found()
    {
        $this->expectException(RegistrationNotFoundException::class);
        $this->expectExceptionMessage('No registration found for builderClass JosKolenberg\LaravelJory\Tests\JoryBuilders\GrrrroupieJoryBuilder. Does JosKolenberg\LaravelJory\Tests\JoryBuilders\GrrrroupieJoryBuilder have an associated Model?');

        $register = app(JoryBuildersRegister::class);

        $register->getByBuilderClass(GrrrroupieJoryBuilder::class);
    }

}
