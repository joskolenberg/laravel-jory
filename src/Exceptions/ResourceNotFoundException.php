<?php


namespace JosKolenberg\LaravelJory\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JosKolenberg\LaravelJory\Helpers\SimilarTextFinder;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

/**
 * Class ResourceNotFoundException
 *
 * Exception to be thrown when a resource is not found by a resource's uri.
 *
 * This exception will result into a 404 to the user.
 */
class ResourceNotFoundException extends \Exception
{
    public function __construct(string $resource)
    {
        $register = app()->make(JoryResourcesRegister::class);

        $message = 'Resource ' . $resource . ' not found, ' . $this->getSuggestion($register->getUrisArray(), $resource);

        parent::__construct($message);
    }

    /**
     * Get the 'Did you mean?' line for the best match in an array of strings.
     *
     * @param array $array
     * @param string $value
     * @return string
     */
    protected function getSuggestion(array $array, string $value): string
    {
        $bestMatch = (new SimilarTextFinder($value, $array))->threshold(4)->first();

        return $bestMatch ? 'did you mean "' . $bestMatch . '"?' : 'no suggestions found.';
    }

    public function render(Request $request): Response
    {
        return response([
            config('jory.response.errors-key') => [
                $this->getMessage(),
            ],
        ], 404);
    }
}