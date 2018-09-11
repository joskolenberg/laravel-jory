<?php
/**
 * Created by PhpStorm.
 * User: joskolenberg
 * Date: 06-09-18
 * Time: 20:56
 */

namespace JosKolenberg\LaravelJory\Parsers;


use Illuminate\Http\Request;
use JosKolenberg\Jory\Contracts\JoryParserInterface;
use JosKolenberg\Jory\Jory;

class RequestParser implements JoryParserInterface
{

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getJory(): Jory
    {
        return (new JsonParser($this->request->input('jory', '{}')))->getJory();
    }
}