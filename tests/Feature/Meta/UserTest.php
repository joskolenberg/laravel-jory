<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Meta;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\Models\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class UserTest extends TestCase
{

    /** @test */
    public function it_can_return_the_current_users_email()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);

        $this->actingAs(User::where('name', 'Bert')->first());

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => ['user'],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street'
            ],
            'meta' => [
                'user' => 'bert@sesamestreet.com',
            ]
        ]);
    }

    /** @test */
    public function it_returns_null_if_no_user_is_logged_in()
    {
        $team = $this->seedSesameStreet();
        Jory::register(TeamJoryResource::class);

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
            ],
            'meta' => ['user'],
        ])->assertStatus(200)->assertExactJson([
            'data' => [
                'name' => 'Sesame Street'
            ],
            'meta' => [
                'user' => null,
            ]
        ]);
    }
}
