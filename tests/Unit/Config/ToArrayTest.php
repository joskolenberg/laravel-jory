<?php

namespace JosKolenberg\LaravelJory\Tests\Unit\Config;

use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Tests\DefaultJoryResources\TeamJoryResource;
use JosKolenberg\LaravelJory\Tests\TestCase;
use JosKolenberg\LaravelJory\Tests\Unit\Config\JoryResources\UserJoryResource;

class ToArrayTest extends TestCase
{

    /** @test */
    public function it_can_give_the_options_for_a_resource()
    {
        Jory::register(UserJoryResource::class);
        Jory::register(TeamJoryResource::class);

        $register = app(JoryResourcesRegister::class);
        $registration = $register->getByUri('user');
        $actual = $registration->getConfig()->toArray();

        $expected = [
            'fields' => [
                [
                    'field' => 'id',
                ],
                [
                    'field' => 'name',
                ],
            ],
            'filters' => [
                [
                    'name' => 'custom_filter',
                    'operators' => [
                        '>',
                        '<',
                    ],
                ],
                [
                    'name' => 'id',
                    'operators' => [
                        '=',
                        '!=',
                        '<>',
                        '>',
                        '>=',
                        '<',
                        '<=',
                        '<=>',
                        'like',
                        'not_like',
                        'is_null',
                        'not_null',
                        'in',
                        'not_in',
                    ],
                ],
                [
                    'name' => 'name',
                    'operators' => [
                        '=',
                    ],
                ],
            ],
            'sorts' => [
                [
                    'name' => 'custom_sort',
                    'default' => [
                        'index' => 2,
                        'order' => 'asc',
                    ],
                ],
                [
                    'name' => 'id',
                    'default' => false,
                ],
                [
                    'name' => 'name',
                    'default' => [
                        'index' => 1,
                        'order' => 'desc',
                    ],
                ],
            ],
            'limit' => [
                'default' => 10,
                'max' => 100,
            ],
            'relations' => [
                [
                    'relation' => 'team',
                    'type' => 'team',
                ],
            ],
        ];

        $this->assertEquals(json_encode($expected), json_encode($actual));
    }
}
