<?php

namespace JosKolenberg\LaravelJory\Routes;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;

class JoryController extends Controller
{
    /**
     * Load a collection for a single resource.
     *
     * @param $uri
     * @param \Illuminate\Http\Request $request
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return mixed
     */
    public function index($uri, Request $request, JoryBuildersRegister $register)
    {
        $registration = $register->getRegistrationByUri($uri);

        if (! $registration) {
            abort(404);
        }

        $modelClass = $registration->getModelClass();

        return $modelClass::jory()->applyRequest($request);
    }

    /**
     * Count the number of items in a resource.
     *
     * @param $uri
     * @param \Illuminate\Http\Request $request
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return mixed
     */
    public function count($uri, Request $request, JoryBuildersRegister $register)
    {
        $registration = $register->getRegistrationByUri($uri);

        if (! $registration) {
            abort(404);
        }

        $modelClass = $registration->getModelClass();

        return $modelClass::jory()->applyRequest($request)->count();
    }

    /**
     * Give a single record.
     *
     * @param $uri
     * @param $id
     * @param \Illuminate\Http\Request $request
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return mixed
     */
    public function show($uri, $id, Request $request, JoryBuildersRegister $register)
    {
        $registration = $register->getRegistrationByUri($uri);

        if (! $registration) {
            abort(404);
        }

        $modelClass = $registration->getModelClass();

        $query = $modelClass::whereKey($id);

        return $modelClass::jory()->applyRequest($request)->onQuery($query)->first();
    }

    /**
     * Give the options for a resource.
     *
     * @param $uri
     * @param \JosKolenberg\LaravelJory\Register\JoryBuildersRegister $register
     * @return mixed
     */
    public function options($uri, JoryBuildersRegister $register)
    {
        $registration = $register->getRegistrationByUri($uri);

        if (! $registration) {
            abort(404);
        }

        $modelClass = $registration->getModelClass();

        return $modelClass::jory()->getConfig();
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
        $jories = $request->all();

        $results = [];
        $errors = [];

        $dataResponseKey = config('jory.response.data-key');
        $errorResponseKey = config('jory.response.errors-key');

        foreach ($jories as $name => $json) {
            $exploded = $this->explodeResourceName($name);
            $modelName = $exploded['modelName'];
            $type = $exploded['type'];
            $alias = $exploded['alias'];
            $id = $exploded['id'];

            $registration = $register->getRegistrationByUri($modelName);

            if (! $registration) {
                $errors[] = 'Resource "'.$modelName.'" is not available, did you mean "'.$this->getSuggestion($register->getUrisArray(), $modelName).'"?';
                continue;
            }
            $modelClass = $registration->getModelClass();

            if ($type === 'count') {
                // Return the count for a resource
                $response = $modelClass::jory()->applyJson($json)->count()->toResponse($request);
            } elseif ($type === 'single') {
                // Return a single item
                $model = $modelClass::find($id);
                if (! $model) {
                    $results[$alias] = null;
                    continue;
                }
                $response = $modelClass::jory()->applyJson($json)->onModel($model)->toResponse($request);
            } else {
                // Return an array of items
                $response = $modelClass::jory()->applyJson($json)->toResponse($request);
            }

            if ($response->getStatusCode() === 422) {
                // Errors occurred, merge all errors into one array prefixed with the resource name
                $currentErrors = $errorResponseKey === null ? $response->getOriginalContent() : $response->getOriginalContent()[$errorResponseKey];
                foreach ($currentErrors as $error) {
                    $errors[] = $name.': '.$error;
                }

                // Continue so we can display all errors for all requested resources
                continue;
            }

            // Everything went well, put result into total array
            $currenData = $dataResponseKey === null ? $response->getOriginalContent() : $response->getOriginalContent()[$dataResponseKey];
            $results[$alias] = $currenData;
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
     * Get the word in an array which looks the most like $value.
     *
     * @param array $array
     * @param string $value
     * @return string
     */
    protected function getSuggestion(array $array, string $value): string
    {
        $bestScore = -1;
        $bestMatch = '';

        foreach ($array as $item) {
            $lev = levenshtein($value, $item);

            if ($lev <= $bestScore || $bestScore < 0) {
                $bestMatch = $item;
                $bestScore = $lev;
            }
        }

        return $bestMatch;
    }

    /**
     * Cut the key into pieces when using "multiple".
     *
     * @param $name
     * @return array
     */
    protected function explodeResourceName($name): array
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

        return compact('modelName', 'alias', 'type', 'id');
    }
}
