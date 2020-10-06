<?php

namespace JosKolenberg\LaravelJory\Tests\Parsers;

use JosKolenberg\LaravelJory\Tests\TestCase;

class RequestParserTest extends TestCase
{
    /** @test */
    public function it_can_get_the_jory_parameter_from_a_request()
    {
        $response = $this->json('GET', 'jory/person', [
            'jory' => '{"flt":{"f": "first_name","d":"John"},"fld":["id","last_name"]}',
        ]);

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                    'id' => 8,
                    'last_name' => 'Bonham',
                ],
                [
                    'id' => 9,
                    'last_name' => 'Lennon',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_defaults_to_empty_when_no_data_is_passed()
    {
        $response = $this->json('GET', 'jory/band');

        $response->assertStatus(200)->assertExactJson([
            'data' => [
                [
                ],
                [
                ],
                [
                ],
                [
                ],
            ],
        ]);
    }
}
