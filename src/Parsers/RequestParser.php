<?php

namespace JosKolenberg\LaravelJory\Parsers;

use Illuminate\Http\Request;
use JosKolenberg\Jory\Contracts\JoryParserInterface;
use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\Jory\Parsers\JsonParser;

/**
 * Class to parse an Laravel request to a Jory query Object.
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
     * Get the jory query object based on the given Request.
     *
     * @return Jory
     */
    public function getJory(): Jory
    {
        $data = $this->request->input(config('jory.request.key'), '{}');

        if (is_array($data)) {
            return (new ArrayParser($data))->getJory();
        }

        if ($this->dataIsBase64($data)) {
            $data = base64_decode($data);
        }

        return (new JsonParser($data))->getJory();
    }

    /**
     * Check if the data is base64 encoded.
     *
     * Might not tell if it's valid but works well enough
     * to make the distinction between base64 and json.
     * And that's all that we need to know here.
     *
     * @param $data
     * @return bool
     */
    function dataIsBase64($data)
    {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $data);
    }
}
