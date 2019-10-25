<?php


namespace JosKolenberg\LaravelJory\Responses;


use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Exceptions\ResourceNotFoundException;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;
use JosKolenberg\LaravelJory\Traits\ProcessesMetadata;
use stdClass;

/**
 * Class JoryMultipleResponse
 *
 * Class for handling a multiple resource Jory call.
 */
class JoryMultipleResponse implements Responsable
{
    use ProcessesMetadata;

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var null|array
     */
    protected $data;
    /**
     * @var JoryResourcesRegister
     */
    protected $register;
    /**
     * @var array
     */
    protected $jories = [];

    /**
     * JoryMultipleResponse constructor.
     * @param Request $request
     * @param JoryResourcesRegister $register
     */
    public function __construct(Request $request, JoryResourcesRegister $register)
    {
        $this->request = $request;
        $this->register = $register;

        $this->initMetadata($request);
    }

    /**
     * Apply an array or Json string.
     *
     * @param $jory
     * @return $this
     */
    public function apply($jory): JoryMultipleResponse
    {
        if (is_array($jory)) {
            return $this->applyArray($jory);
        }

        if (!is_string($jory)) {
            throw new LaravelJoryException('Unexpected type given. Please provide an array or Json string.');
        }

        return $this->applyJson($jory);
    }

    /**
     * Apply a json string.
     *
     * @param string $jory
     * @return $this
     */
    public function applyJson(string $jory): JoryMultipleResponse
    {
        $array = json_decode($jory, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            /**
             * No use for further processing when json is not valid, abort.
             */
            throw new LaravelJoryCallException(['Jory string is no valid json.']);
        }

        $this->data = $array;

        return $this;
    }

    /**
     * Apply an array.
     *
     * @param array $jory
     * @return $this
     */
    public function applyArray(array $jory): JoryMultipleResponse
    {
        $this->data = $jory;

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     * @return Response
     */
    public function toResponse($request)
    {
        $data = $this->toArray();

        $responseKey = config('jory.response.data-key');
        $response = $responseKey === null ? $data : [$responseKey => $data];

        $meta = $this->getMetadata();
        if($responseKey !== null && $meta !== null){
            $response[$this->getMetaResponseKey()] = $meta;
        }

        return response($response);
    }

    /**
     * Collect all the data for the requested resources.
     *
     * @return array
     */
    public function toArray(): array
    {
        // Convert the raw data into jory queries.
        if (empty($this->jories)) {
            $this->dataIntoJories();
        }

        //Process all Jorie queries.
        $results = [];
        $errors = [];
        foreach ($this->jories as $single) {
            try {
                $results[$single->alias] = $this->processSingle($single);
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

    /**
     * Process the raw request data into the jories array.
     */
    protected function dataIntoJories(): void
    {
        if (!$this->data) {
            // If no explicit data is set, we default to the data in the request.
            $this->apply($this->request->input(config('jory.request.key'), '{}'));
        }

        /**
         * Each item in the array should hold a key as the resource name
         * and value with a jory query array or json string.
         * Add the individual requested resources to the jories array.
         */
        $errors = [];
        foreach ($this->data as $name => $data) {
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

        if (!empty($errors)) {
            throw new LaravelJoryCallException($errors);
        }
    }

    /**
     * Add a jory request to the array.
     *
     * @param string $name
     * @param array $data
     */
    protected function addJory(string $name, array $data): void
    {
        $exploded = ResourceNameHelper::explode($name);

        $single = new stdClass();
        $single->name = $name;
        $single->data = $data;
        $single->resource = $this->register->getByUri($exploded->baseName);
        $single->alias = $exploded->alias;
        $single->type = $exploded->type;
        $single->id = $exploded->id;

        $this->jories[] = $single;
    }

    /**
     * Process a single resource call. We'll just use a normal single
     * JoryResponse and use the toArray() method to collect the data.
     *
     * @param $single
     * @return mixed
     */
    protected function processSingle($single)
    {
        $singleResponse = Jory::byUri($single->resource->getUri());

        $singleResponse->apply($single->data);

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

        return $singleResponse->toArray();
    }
}