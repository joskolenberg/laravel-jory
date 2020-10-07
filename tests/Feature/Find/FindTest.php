<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Find;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\TestCase;

class FindTest extends TestCase
{

    /** @test */
    public function it_returns_a_404_when_a_model_is_not_found_by_id()
    {
        Jory::register(UserJoryResource::class);

        $this->json('GET', 'jory/user/1', [
            'jory' => [
                'fld' => 'name',
            ]
        ])->assertStatus(404)->assertExactJson([
            'message' => 'No query results for model [' . User::class . '] 1',
        ]);
    }

}
