<?php

namespace JosKolenberg\LaravelJory\Tests\Feature\Query\Filter;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Tests\Feature\Query\Filter\JoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\Feature\Query\Filter\JoryResources\UserJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;

class FilterTest extends TestCase
{

    /** @test */
    public function it_can_apply_a_single_filter()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'flt' => [
                    'f' => 'name',
                    'o' => 'like',
                    'd' => '%a%',
                ],
                'fld' => ['name'],
            ],
        ])->assertExactJson([
            'data' => [
                ['name' => 'Oscar'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_an_OR_filter_group()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'flt' => [
                    'or' => [
                        [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%b%',
                        ],
                        [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%a%',
                        ],
                    ],
                ],
                'fld' => ['name'],
            ],
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Big Bird'],
                ['name' => 'Oscar'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_an_AND_filter_group()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'flt' => [
                    'and' => [
                        [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%b%',
                        ],
                        [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%d%',
                        ],
                    ],
                ],
                'fld' => ['name'],
            ],
        ])->assertExactJson([
            'data' => [
                ['name' => 'Big Bird'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_nested_filters_1()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'flt' => [
                    'and' => [
                        [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%a%',
                        ]
                    ]
                ],
                'fld' => ['name'],
            ],
        ])->assertExactJson([
            'data' => [
                [
                    'name' => 'Oscar',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_nested_filters_2()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'flt' => [
                    'and' => [
                        [
                            'f' => 'name',
                            'o' => 'like',
                            'd' => '%e%',
                        ],
                        [
                            'or' => [
                                [
                                    'f' => 'name',
                                    'o' => 'like',
                                    'd' => '%b%',
                                ],
                                [
                                    'f' => 'name',
                                    'o' => 'like',
                                    'd' => '%r%',
                                ],
                            ],
                        ],
                    ],
                ],
                'fld' => ['name'],
            ],
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
                ['name' => 'Cookie Monster'],
                ['name' => 'Ernie'],
            ],
        ]);
    }

    /** @test */
    public function it_doesnt_apply_any_filter_when_parameter_is_omitted()
    {
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/team', [
            'jory' => [
                'fld' => 'name'
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Sesame Street'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_a_custom_filterScope()
    {
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();

        $this->json('GET', 'jory/team', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'has_user_with_name',
                    'd' => 'Homer',
                ]
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Simpsons'],
            ],
        ]);
    }

    /** @test */
    public function it_wraps_a_closure_around_custom_orWheres_to_prevent_returning_unwanted_data()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'and' => [
                        [
                            'f' => 'name',
                            'o' => 'in',
                            'd' => ['Bert', 'Oscar'],
                        ],
                        [
                            'f' => 'bert_and_ernie',
                        ],
                    ]
                ]
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Bert'],
            ],
        ]);
    }

    /** @test */
    public function it_can_filter_on_a_related_models_field()
    {
        Jory::register(TeamJoryResource::class);
        $this->seedSesameStreet();
        $this->seedSimpsons();

        $this->json('GET', 'jory/team', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'users.name',
                    'd' => 'Homer',
                ]
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Simpsons'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_via_the_field_using_a_filter_scope_class()
    {
        Jory::register(UserJoryResource::class);
        $this->seedSesameStreet();

        $this->json('GET', 'jory/user', [
            'jory' => [
                'fld' => 'name',
                'flt' => [
                    'f' => 'oscar',
                ]
            ]
        ])->assertExactJson([
            'data' => [
                ['name' => 'Oscar'],
            ],
        ]);
    }

    /** @test */
    public function it_can_apply_via_the_field_using_a_filter_scope_class_when_requesting_a_relation()
    {
        Jory::register(TeamJoryResource::class);
        Jory::register(UserJoryResource::class);
        $team = $this->seedSesameStreet();

        $this->json('GET', 'jory/team/' . $team->id, [
            'jory' => [
                'fld' => 'name',
                'rlt' => [
                    'users' => [
                        'fld' => 'name',
                        'flt' => [
                            'f' => 'oscar',
                        ]
                    ]
                ]
            ]
        ])->assertExactJson([
            'data' => [
                'name' => 'Sesame Street',
                'users' => [
                    ['name' => 'Oscar']
                ]
            ],
        ]);
    }
}
