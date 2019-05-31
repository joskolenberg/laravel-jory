<?php

namespace JosKolenberg\LaravelJory\Exceptions;

/**
 * Exception class to be thrown when an error occurs
 * because of invalid data passed in the jory query.
 *
 * These exceptions will be used to show the errors in the return data
 * and will result into a 404 to the user by the JoryHandler.
 *
 * Class LaravelJoryCallException
 */
class LaravelJoryCallException extends \Exception
{
    protected $errors = [];

    public function __construct(array $errors, string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
