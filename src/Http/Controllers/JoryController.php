<?php

namespace JosKolenberg\LaravelJory\Http\Controllers;

use SimilarText\Finder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;

class JoryController extends Controller
{
    /**
     * Load a collection for a single resource.
     *
     * @param $resource
     * @param \Illuminate\Http\Request $request
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return mixed
     */
    public function index($resource, Request $request, JoryBuildersRegister $register)
    {
        $registration = $register->getByUri($resource);

        if (! $registration) {
            return $this->abortWithErrors(['Resource ' . $resource . ' not found, ' . $this->getSuggestion($register->getUrisArray(), $resource)], 404);
        }

        $modelClass = $registration->getModelClass();
        $joryBuilderClass = $registration->getBuilderClass();

        return (new $joryBuilderClass())
            ->onQuery($modelClass::query())
            ->applyRequest($request);
    }

    /**
     * Count the number of items in a resource.
     *
     * @param $resource
     * @param \Illuminate\Http\Request $request
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return mixed
     */
    public function count($resource, Request $request, JoryBuildersRegister $register)
    {
        $registration = $register->getByUri($resource);

        if (! $registration) {
            return $this->abortWithErrors(['Resource ' . $resource . ' not found, ' . $this->getSuggestion($register->getUrisArray(), $resource)], 404);
        }

        $modelClass = $registration->getModelClass();
        $joryBuilderClass = $registration->getBuilderClass();

        return (new $joryBuilderClass())
            ->onQuery($modelClass::query())
            ->applyRequest($request)
            ->count();
    }

    /**
     * Give a single record.
     *
     * @param $resource
     * @param $id
     * @param \Illuminate\Http\Request $request
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return mixed
     */
    public function show($resource, $id, Request $request, JoryBuildersRegister $register)
    {
        $registration = $register->getByUri($resource);

        if (! $registration) {
            return $this->abortWithErrors(['Resource ' . $resource . ' not found, ' . $this->getSuggestion($register->getUrisArray(), $resource)], 404);
        }

        $modelClass = $registration->getModelClass();
        $joryBuilderClass = $registration->getBuilderClass();

        return (new $joryBuilderClass())
            ->onQuery($modelClass::whereKey($id))
            ->applyRequest($request)
            ->first();
    }

    /**
     * Give the options for a resource.
     *
     * @param $resource
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return mixed
     */
    public function options($resource, JoryBuildersRegister $register)
    {
        $registration = $register->getByUri($resource);

        if (! $registration) {
            return $this->abortWithErrors(['Resource ' . $resource . ' not found, ' . $this->getSuggestion($register->getUrisArray(), $resource)], 404);
        }

        $joryBuilderClass = $registration->getBuilderClass();

        return (new $joryBuilderClass())->getConfig();
    }

    /**
     * Load multiple resources at once.
     *
     * @param \Illuminate\Http\Request $request
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     */
    public function multiple(Request $request, JoryBuildersRegister $register)
    {
        $results = [];
        $errors = [];

        $dataResponseKey = config('jory.response.data-key');
        $errorResponseKey = config('jory.response.errors-key');

        $data = $request->input(config('jory.request.key'), '{}');

        if (is_array($data)) {
            $jories = $data;
        }else{
            $jories = json_decode($data, true);


            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'Jory string is no valid json.';
            }
        }

        $explodedJories = [];

        if(!$errors){
            // Not needed when there are already errors.
            foreach ($jories as $name => $data) {
                $single = $this->explodeResourceName($name);

                $registration = $register->getByUri($single->modelName);

                if (! $registration) {
                    $errors[] = 'Resource "'.$single->modelName.'" is not available, '.$this->getSuggestion($register->getUrisArray(), $single->modelName);
                    continue;
                }

                $single->registration = $registration;
                $single->data = $data;
                $single->name = $name;

                $explodedJories[] = $single;
            }
        }

        if (! $errors) {
            // Don't perform any queries when there is already an error somewhere
            foreach ($explodedJories as $single) {
                $modelClass = $single->registration->getModelClass();
                $joryBuilderClass = $single->registration->getBuilderClass();

                $joryBuilder = new $joryBuilderClass();

                if ($single->type === 'count') {
                    // Return the count for a resource
                    $response = $this->applyArrayOrJson($joryBuilder, $single->data)
                        ->onQuery($modelClass::query())
                        ->count()
                        ->toResponse($request);
                } elseif ($single->type === 'single') {
                    // Return a single item
                    $query = $modelClass::whereKey($single->id);

                    $response = $this->applyArrayOrJson($joryBuilder, $single->data)
                        ->onQuery($query)
                        ->first()
                        ->toResponse($request);
                } else {
                    // Return an array of items
                    $response = $this->applyArrayOrJson($joryBuilder, $single->data)
                        ->onQuery($modelClass::query())
                        ->toResponse($request);
                }

                if ($response->getStatusCode() === 422) {
                    // Errors occurred, merge all errors into one array prefixed with the resource name
                    $currentErrors = $errorResponseKey === null ? $response->getOriginalContent() : $response->getOriginalContent()[$errorResponseKey];
                    foreach ($currentErrors as $error) {
                        $errors[] = $single->name.': '.$error;
                    }

                    // Continue so we can display all errors for all requested resources
                    continue;
                }

                // Everything went well, put result into total array
                $currenData = $dataResponseKey === null ? $response->getOriginalContent() : $response->getOriginalContent()[$dataResponseKey];
                $results[$single->alias] = $currenData;
            }
        }

        if (count($errors) > 0) {
            // If only one error occurred, return only errors
            // All data must be valid to get a 200 response

            $response = $errorResponseKey === null ? $errors : [$errorResponseKey => $errors];

            return response($response, 422);
        }

        // Everything went well, return result
        $response = $dataResponseKey === null ? $results : [$dataResponseKey => $results];

        return response($response);
    }

    /**
     * Display a list of available resources.
     *
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function resourceList(JoryBuildersRegister $register)
    {
        return response(['resources' => $register->getUrisArray()]);
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
        $bestMatch = (new Finder($value, $array))->threshold(4)->first();

        return $bestMatch ? 'did you mean "' . $bestMatch . '"?' : 'no suggestions found.';
    }

    /**
     * Cut the key into pieces when using "multiple".
     *
     * @param $name
     * @return \stdClass
     */
    protected function explodeResourceName($name): \stdClass
    {
        $nameParts = explode(' as ', $name);

        if (count($nameParts) === 1) {
            $modelName = $nameParts[0];
            $alias = $nameParts[0];
        } else {
            $modelName = $nameParts[0];
            $alias = $nameParts[1];
        }

        $nameParts = explode(':', $modelName);

        if (count($nameParts) === 1) {
            $type = 'multiple';
            $id = null;
        } elseif ($nameParts[1] === 'count') {
            $type = 'count';
            $modelName = $nameParts[0];
            $id = null;
        } else {
            $type = 'single';
            $modelName = $nameParts[0];
            $id = $nameParts[1];
        }

        $result = new \stdClass();
        $result->modelName = $modelName;
        $result->alias = $alias;
        $result->type = $type;
        $result->id = $id;

        return $result;
    }

    /**
     * Apply given data from the request to the JoryBuilder.
     *
     * @param \JosKolenberg\LaravelJory\JoryBuilder $joryBuilder
     * @param mixed $data
     * @return \JosKolenberg\LaravelJory\JoryBuilder
     */
    protected function applyArrayOrJson(JoryBuilder $joryBuilder, $data): JoryBuilder
    {
        if (is_array($data)) {
            $joryBuilder->applyArray($data);
        } else {
            $joryBuilder->applyJson($data);
        }

        return $joryBuilder;
    }

    /**
     * Return a response to abort the request with errors.
     *
     * @param array $errors
     * @param int $statusCode
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function abortWithErrors(array $errors, int $statusCode)
    {
        $errorResponseKey = config('jory.response.errors-key');

        $response = $errorResponseKey === null ? $errors : [$errorResponseKey => $errors];

        return response($response, $statusCode);
    }
}
