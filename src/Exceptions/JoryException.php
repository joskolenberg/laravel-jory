<?php

namespace JosKolenberg\LaravelJory\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Exception class to be thrown when an error occurs in the Jory package (e.g. when validating).
 * Rethrow with this class to render a proper response.
 *
 * Class JoryException
 */
class JoryException extends \Exception
{
    /**
     * The original exception form the Jory Package
     *
     * @var \JosKolenberg\Jory\Exceptions\JoryException $original
     */
    private $original;

    public function __construct(\JosKolenberg\Jory\Exceptions\JoryException $original)
    {
        $this->original = $original;
    }

    public function render(Request $request): Response
    {
        return response([
            config('jory.response.errors-key') => [
                $this->original->getMessage(),
            ],
        ], 422);
    }
}
