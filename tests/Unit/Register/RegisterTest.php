<?php

namespace JosKolenberg\LaravelJory\Tests\Unit\Register;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\BandJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\SongJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\AutoRegistered\TagJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\AlbumCoverJoryResourceWithoutRoutes;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\CustomSongJoryResource;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\CustomSongJoryResource2;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithConfig;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithConfigThree;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\SongJoryResourceWithConfigTwo;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\TagJoryResourceWithExplicitSelect;
use JosKolenberg\LaravelJory\Tests\Models\Model;
use JosKolenberg\LaravelJory\Tests\Models\User;
use JosKolenberg\LaravelJory\Tests\TestCase;
use JosKolenberg\LaravelJory\Tests\Unit\Register\JoryResources\Autoload\AutoloadedUserJoryResource;
use JosKolenberg\LaravelJory\Tests\Unit\Register\JoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\Unit\Register\JoryResources\BandJoryResourceWithoutRoutes;
use JosKolenberg\LaravelJory\Tests\Unit\Register\JoryResources\UserJoryResourceWithoutRoutes;

class RegisterTest extends TestCase
{

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('jory.auto-registrar', [
            'namespace' => 'JosKolenberg\LaravelJory\Tests\Unit\Register\JoryResources\Autoload',
            'path' => __DIR__ . DIRECTORY_SEPARATOR . 'JoryResources' . DIRECTORY_SEPARATOR . 'Autoload',
        ]);
    }

    /** @test */
    public function it_gives_manually_added_jory_resources_precedence_over_autoregistered_jory_resources_with_the_same_uri()
    {
        $register = app(JoryResourcesRegister::class);
        $this->assertInstanceOf(AutoloadedUserJoryResource::class, $register->getByUri('user'));

        Jory::register(UserJoryResource::class);

        $this->assertInstanceOf(UserJoryResource::class, $register->getByUri('user'));
    }

    /** @test */
    public function it_gives_any_newly_added_jory_resources_precedence_over_earlier_registered_jory_resources_with_the_same_uri()
    {
        $register = app(JoryResourcesRegister::class);

        Jory::register(UserJoryResource::class);
        $this->assertInstanceOf(UserJoryResource::class, $register->getByUri('user'));

        Jory::register(AutoloadedUserJoryResource::class);
        $this->assertInstanceOf(AutoloadedUserJoryResource::class, $register->getByUri('user'));
    }

    /** @test */
    public function it_can_give_all_the_available_resources()
    {
        $register = app(JoryResourcesRegister::class);
        Jory::register(BandJoryResource::class);

        $actual = $register->getUrisArray();

        $expected = [
            'band',
            'team',
            'user',
        ];

        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /** @test */
    public function it_doesnt_return_a_jory_resource_without_routes_enabled()
    {
        $register = app(JoryResourcesRegister::class);
        Jory::register(BandJoryResourceWithoutRoutes::class);
        Jory::register(UserJoryResourceWithoutRoutes::class);

        $actual = $register->getUrisArray();

        $expected = [
            'team',
        ];

        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /** @test */
    public function a_jory_resource_without_routes_enabled_cannot_be_called_from_the_uri()
    {
        Jory::register(UserJoryResourceWithoutRoutes::class);

        $this->json('GET', 'jory/user', [
            'jory' => [],
        ])->assertStatus(404);
    }

    /** @test */
    public function a_jory_resource_without_routes_enabled_can_still_be_used_to_query_relations()
    {
        $team = $this->seedSesameStreet();
        Jory::register(UserJoryResourceWithoutRoutes::class);

        $response = $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => ['name'],
                'rlt' => [
                    'users:first' => [
                        'fld' => 'name',
                    ]
                ]
            ]
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users:first' => [
                    'name' => 'Bert'
                ],
            ],
        ]);
    }
}
