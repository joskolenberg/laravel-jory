<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Filter;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\DefaultModels\User;
use JosKolenberg\LaravelJory\Tests\JoryResources\Unregistered\PersonJoryResourceWithScopes;
use JosKolenberg\LaravelJory\Tests\Models\Band;
use JosKolenberg\LaravelJory\Tests\Models\Person;
use JosKolenberg\LaravelJory\Tests\Models\Song;
use JosKolenberg\LaravelJory\Tests\TestCase;

class FilterOperatorTest extends TestCase
{

    /** @test */
    public function it_can_filter_by_the_equals_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => '=',
                    'd' => 'Ernie',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Ernie'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_by_the_greater_than_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => '>',
                    'd' => 'F',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Oscar'],
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_by_the_lower_than_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => '<',
                    'd' => 'C',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_by_the_not_equal_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => '<>',
                    'd' => 'Big Bird',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Cookie Monster'],
                ['name' => 'Ernie'],
                ['name' => 'Oscar'],
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_by_the_alternate_not_equal_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => '!=',
                    'd' => 'Big Bird',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Cookie Monster'],
                ['name' => 'Ernie'],
                ['name' => 'Oscar'],
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_by_the_like_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => 'Big Bird',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Big Bird'],
            ],
        ]);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => 'b%',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird'],
            ],
        ]);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%r',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Cookie Monster'],
                ['name' => 'Oscar'],
            ],
        ]);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%r%',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird'],
                ['name' => 'Cookie Monster'],
                ['name' => 'Ernie'],
                ['name' => 'Oscar'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_by_the_not_like_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'not_like',
                    'd' => '%r%',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_by_the_greater_than_or_equal_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => '>=',
                    'd' => 'Oscar',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Oscar'],
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_by_the_lower_than_or_equal_operators(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => '<=',
                    'd' => 'Big Bird',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_on_null_values(){
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        User::factory()->create([
            'name' => 'Dexter',
        ]);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'team_id',
                    'o' => 'is_null',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Dexter'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_on_non_null_values()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        User::factory()->create([
            'name' => 'Dexter',
        ]);

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'team_id',
                    'o' => 'not_null',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird'],
                ['name' => 'Cookie Monster'],
                ['name' => 'Ernie'],
                ['name' => 'Oscar'],
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_an_IN_filter()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'in',
                    'd' => [
                        'Bert',
                        'Ernie',
                    ]
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Ernie'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_a_NOT_IN_filter()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'o' => 'not_in',
                    'd' => [
                        'Bert',
                        'Ernie',
                    ]
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Big Bird'],
                ['name' => 'Cookie Monster'],
                ['name' => 'Oscar'],
                ['name' => 'The Count'],
            ],
        ]);
    }

    /** @test */
    public function it_defaults_to_an_EQUALS_check_if_no_operator_is_given()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'name',
                    'd' => 'Bert',
                ],
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
            ],
        ]);
    }
}
