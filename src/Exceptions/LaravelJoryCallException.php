<?php

namespace JosKolenberg\LaravelJory\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Exception class to be thrown when an error occurs
 * because of invalid data passed in the jory query.
 *
 * These exceptions will be used to show the errors in the
 * return data and will result into a 422 to the user.
 *
 * Class LaravelJoryCallException
 */
class LaravelJoryCallException extends \Exception
{
    protected $errors = [];

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct(implode(', ', $errors));
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function render(Request $request): Response
    {
        $responseKey = config('jory.response.errors-key');
        $response = $responseKey === null ? $this->getErrors() : [$responseKey => $this->getErrors()];

        return response($response, 422);
    }
}
