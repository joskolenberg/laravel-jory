<?php

namespace JosKolenberg\LaravelJory\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use JosKolenberg\Jory\Exceptions\JoryException;

/**
 * Class JoryHandler
 *
 * Error handler for Jory routes.
 */
class JoryHandler extends ExceptionHandler
{

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof JoryException) {
            return response([
                $this->getErrorResponseKey() => [
                    $exception->getMessage(),
                ],
            ], 422);
        }

        if ($exception instanceof ResourceNotFoundException) {
            return response([
                $this->getErrorResponseKey() => [
                    $exception->getMessage(),
                ],
            ], 404);
        }

        if ($exception instanceof LaravelJoryCallException) {
            $responseKey = $this->getErrorResponseKey();
            $response = $responseKey === null ? $exception->getErrors() : [$responseKey => $exception->getErrors()];

            return response($response, 422);
        }

        return parent::render($request, $exception);
    }

    /**
     * Get the key on which errors should be returned.
     *
     * @return null|string
     */
    protected function getErrorResponseKey(): ?string
    {
        return config('jory.response.errors-key');
    }
}
