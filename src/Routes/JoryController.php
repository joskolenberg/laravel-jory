<?php

namespace JosKolenberg\LaravelJory\Routes;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JoryController extends Controller
{
    /**
     * Load a collection for a single resource.
     *
     * @param $uri
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function index($uri, Request $request)
    {
        $modelClass = config('jory.routes.'.$uri);

        if (! $modelClass) {
            abort(404);
        }

        return $modelClass::jory()->applyRequest($request);
    }

    /**
     * Count the number of items in a resource.
     *
     * @param $uri
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function count($uri, Request $request)
    {
        $modelClass = config('jory.routes.'.$uri);

        if (! $modelClass) {
            abort(404);
        }

        return $modelClass::jory()->applyRequest($request)->count();
    }

    /**
     * Give a single record.
     *
     * @param $uri
     * @param $id
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function show($uri, $id, Request $request)
    {
        $modelClass = config('jory.routes.'.$uri);

        if (! $modelClass) {
            abort(404);
        }

        $model = $modelClass::findOrFail($id);

        return $modelClass::jory()->applyRequest($request)->onModel($model);
    }

    /**
     * Give the options for a resource.
     *
     * @param $uri
     * @return mixed
     */
    public function options($uri)
    {
        $modelClass = config('jory.routes.'.$uri);

        if (! $modelClass) {
            abort(404);
        }

        return $modelClass::jory()->getBlueprint();
    }

    /**
     * Load multiple resources at once.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     */
    public function multiple(Request $request)
    {
        $jories = $request->all();

        $results = [];
        $errors = [];

        foreach ($jories as $name => $json) {
            $exploded = $this->explodeResourceName($name);
            $modelName = $exploded['modelName'];
            $type = $exploded['type'];
            $alias = $exploded['alias'];
            $id = $exploded['id'];

            $modelClass = config('jory.routes.'.$modelName);

            if (! $modelClass) {
                $errors[] = 'Resource "'.$modelName.'" is not available, did you mean "'.$this->getSuggestion(array_keys(config('jory.routes')), $modelName).'"?';
                continue;
            }

            if ($type === 'count') {
                // Return the count for a resource
                $response = $modelClass::jory()->applyJson($json)->count()->toResponse($request);
            } elseif ($type === 'single') {
                // Return a single item
                $model = $modelClass::find($id);
                if (! $model) {
                    $errors[] = 'Resource with id '.$id.' was not found on resource '.$modelName;
                    continue;
                }
                $response = $modelClass::jory()->applyJson($json)->onModel($model)->toResponse($request);
            } else {
                // Return an array of items
                $response = $modelClass::jory()->applyJson($json)->toResponse($request);
            }

            if ($response->getStatusCode() === 422) {
                // Errors occurred, merge all errors into one array prefixed with the resource name
                foreach ($response->getOriginalContent()['errors'] as $error) {
                    $errors[] = $name.': '.$error;
                }

                // Continue so we can display all errors for all requested resources
                continue;
            }

            // Everything went well, put result into total array
            $results[$alias] = $response->getOriginalContent()['data'];
        }

        if (count($errors) > 0) {
            // If only one error occurred, return only errors
            // All data must be valid to get a 200 response
            return response(['errors' => $errors], 422);
        }

        // Everything went well, return result
        return response(['data' => $results]);
    }

    /**
     * Display a list of available resources.
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function resourceList()
    {
        return response(['resources' => array_keys(config('jory.routes'))]);
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
    protected function explodeResourceName($name)
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

        return compact("modelName", "alias", "type", "id");
    }
}
