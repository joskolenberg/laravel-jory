<?php


namespace JosKolenberg\LaravelJory\Responses;


use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Exceptions\ResourceNotFoundException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;
use stdClass;

/**
 * Class JoryMultipleResponse
 *
 * Class for handling a multiple resource Jory call.
 */
class JoryMultipleResponse implements Responsable
{

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var JoryBuildersRegister
     */
    protected $register;
    /**
     * @var array
     */
    protected $jories = [];

    /**
     * JoryMultipleResponse constructor.
     * @param Request $request
     * @param JoryBuildersRegister $register
     */
    public function __construct(Request $request, JoryBuildersRegister $register)
    {
        $this->request = $request;
        $this->register = $register;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     * @return Response
     * @throws JoryException
     * @throws LaravelJoryCallException
     * @throws LaravelJoryException
     */
    public function toResponse($request)
    {
        $data = $this->toArray();

        $dataResponseKey = config('jory.response.data-key');

        return response($dataResponseKey === null ? $data : [$dataResponseKey => $data]);
    }

    /**
     * Cut the key into pieces when using "multiple".
     *
     * @param $name
     * @return stdClass
     */
    protected function explodeResourceName($name): stdClass
    {
        /**
         * First cut the alias part
         */
        $nameParts = explode(' as ', $name);
        if (count($nameParts) === 1) {
            $modelName = $nameParts[0];
            $alias = $nameParts[0];
        } else {
            $modelName = $nameParts[0];
            $alias = $nameParts[1];
        }

        /**
         * Next, check the type of request.
         */
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

        /**
         * Create the value object.
         */
        $result = new stdClass();
        $result->modelName = $modelName;
        $result->alias = $alias;
        $result->type = $type;
        $result->id = $id;

        return $result;
    }

    /**
     * Process the raw request data into the jories array.
     *
     * @throws LaravelJoryCallException
     */
    protected function requestIntoJories()
    {
        $data = $this->request->input(config('jory.request.key'), '{}');

        /**
         * First check if there is any data and
         * if it's valid json or array.
         */
        if (is_array($data)) {
            $jories = $data;
        } else {
            $jories = json_decode($data, true);


            if (json_last_error() !== JSON_ERROR_NONE) {
                /**
                 * No use for further processing when json is not valid, abort.
                 */
                throw new LaravelJoryCallException(['Jory string is no valid json.']);
            }
        }

        /**
         * Each item in the array should hold a key as the resource name
         * and value with a jory string.
         * Add the individual requested resources to the jories array.
         */
        $errors = [];
        foreach ($jories as $name => $data) {
            try {
                $this->addJory($name, $data);
            } catch (ResourceNotFoundException $e) {
                /**
                 * When multiple resources are not found, we'd like
                 * to show all the not found errors to the user.
                 * So collect them here and throw them all
                 * at once later on.
                 */
                $errors[] = $e->getMessage();
                continue;
            }
        }

        if(!empty($errors)){
            throw new LaravelJoryCallException($errors);
        }
    }

    /**
     * Add a jory request to the array.
     *
     * @param string $name
     * @param array $data
     * @throws ResourceNotFoundException
     */
    protected function addJory(string $name, array $data)
    {
        $exploded = $this->explodeResourceName($name);

        $single = new stdClass();
        $single->name = $name;
        $single->data = $data;
        $single->registration = $this->register->getByUri($exploded->modelName);
        $single->alias = $exploded->alias;
        $single->type = $exploded->type;
        $single->id = $exploded->id;

        $this->jories[] = $single;
    }

    /**
     * Process a single resource call.
     * We'll just use a normal single JoryResponse and use the getResult()
     * method instead of toResponse() to collect the data.
     *
     * @param $single
     * @return mixed
     * @throws LaravelJoryCallException
     * @throws JoryException
     * @throws LaravelJoryException
     */
    protected function processResource($single)
    {
        $singleResponse = Jory::byUri($single->registration->getUri());

        /**
         * Request may consist of json or array, so check this.
         */
        if (is_array($single->data)) {
            $singleResponse->applyArray($single->data);
        } else {
            $singleResponse->applyJson($single->data);
        }

        /**
         * Call appropriate methods for specific types.
         */
        if ($single->type === 'count') {
            $singleResponse->count();
        }
        if ($single->type === 'single') {
            // Return a single item
            $singleResponse->find($single->id);
        }

        return $singleResponse->getResult();
    }

    /**
     * Collect all the data for the requested resources.
     *
     * @return array
     * @throws JoryException
     * @throws LaravelJoryCallException
     * @throws LaravelJoryException
     */
    protected function toArray(): array
    {
        /**
         * If no Jories were manually added before, use the data from the request.
         */
        if (empty($this->jories)) {
            $this->requestIntoJories();
        }

        /**
         * Process all Jories.
         */
        $results = [];
        $errors = [];
        foreach ($this->jories as $single) {
            try {
                $results[$single->alias] = $this->processResource($single);
            } catch (LaravelJoryCallException $e) {
                foreach ($e->getErrors() as $error) {
                    /**
                     * When multiple requests result in errors, we'd like
                     * to show all the errors that occurred to the user.
                     * So collect them here and throw them all
                     * at once later on.
                     */
                    $errors[] = $single->name . ': ' . $error;
                }
            }
        }

        if (count($errors) > 0) {
            throw new LaravelJoryCallException($errors);
        }

        return $results;
    }
}