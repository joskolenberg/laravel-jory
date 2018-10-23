<?php

namespace JosKolenberg\LaravelJory\Parsers;

use JosKolenberg\Jory\Jory;
use Illuminate\Http\Request;
use JosKolenberg\Jory\Parsers\JsonParser;
use JosKolenberg\Jory\Contracts\JoryParserInterface;

/**
 * Class to parse an Laravel request to a Jory Object.
 * Uses the "jory" parameter which must hold the jory data in json format.
 *
 * Class RequestParser
 */
class RequestParser implements JoryParserInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * RequestParser constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the jory object based on the given Request.
     *
     * @return Jory
     */
    public function getJory(): Jory
    {
        return (new JsonParser($this->request->input(config('jory.request-key'), '{}')))->getJory();
    }
}
