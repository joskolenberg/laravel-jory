<?php


namespace JosKolenberg\LaravelJory\Responses;


use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use JosKolenberg\Jory\Contracts\JoryParserInterface;
use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Parsers\ArrayParser;
use JosKolenberg\Jory\Parsers\JsonParser;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryException;
use JosKolenberg\LaravelJory\Exceptions\RegistrationNotFoundException;
use JosKolenberg\LaravelJory\Exceptions\ResourceNotFoundException;
use JosKolenberg\LaravelJory\JoryBuilder;
use JosKolenberg\LaravelJory\JoryResource;
use JosKolenberg\LaravelJory\Parsers\RequestParser;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

/**
 * Class JoryResponse
 *
 * Class to turn jory requests into responses.
 */
class JoryResponse implements Responsable
{

    /**
     * @var JoryResourcesRegister
     */
    protected $register;

    /**
     * @var JoryResource
     */
    protected $joryResource;

    /**
     * @var JoryParserInterface
     */
    protected $parser;

    /**
     * @var bool
     */
    protected $count = false;

    /**
     * @var mixed
     */
    protected $modelId;

    /**
     * @var bool
     */
    protected $first = false;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * JoryResponse constructor.
     * @param Request $request
     * @param JoryResourcesRegister $register
     */
    public function __construct(Request $request, JoryResourcesRegister $register)
    {
        $this->register = $register;
        $this->request = $request;
    }

    /**
     * Set the resource to be called based on the uri.
     *
     * @param string $uri
     * @return $this
     * @throws ResourceNotFoundException
     */
    public function byUri(string $uri): JoryResponse
    {
        $this->joryResource = $this->register->getByUri($uri)->fresh();

        return $this;
    }

    /**
     * Set the resource to be called based on the model class.
     *
     * @param string $modelClass
     * @return $this
     * @throws RegistrationNotFoundException
     */
    public function onModelClass(string $modelClass): JoryResponse
    {
        $this->joryResource = $this->register->getByModelClass($modelClass)->fresh();

        return $this;
    }

    /**
     * Set the resource to be called based on a model instance,
     * the builder will be set to return a single record instead of a collection.
     *
     * @param Model $model
     * @return JoryResponse
     * @throws RegistrationNotFoundException
     */
    public function onModel(Model $model): JoryResponse
    {
        $this->joryResource = $this->register->getByModelClass(get_class($model))->fresh();

        /**
         * When an existing model is given, we simply set the id to filter on.
         * The model wil be queried again from te database when executing
         * the response, but this way we are sure to have consistent
         * data in the model (at the cost of an extra query though).
         */
        $this->find($model->getKey());

        return $this;
    }

    /**
     * Set an existing query to build upon.
     *
     * @param Builder $query
     * @return JoryResponse
     * @throws RegistrationNotFoundException
     */
    public function onQuery(Builder $query): JoryResponse
    {
        $this->joryResource = $this->register->getByModelClass(get_class($query->getModel()))->fresh();
        $this->query = $query;

        return $this;
    }

    /**
     * Helper function to apply an array or Json string.
     *
     * @param mixed $jory
     * @return JoryResponse
     * @throws LaravelJoryException
     */
    public function apply($jory): JoryResponse
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
     * Apply a Json Jory query string to the response.
     *
     * @param string $json
     * @return $this
     */
    public function applyJson(string $json): JoryResponse
    {
        $this->parser = new JsonParser($json);

        return $this;
    }

    /**
     * Apply a Jory query array to the response.
     *
     * @param array $array
     * @return $this
     */
    public function applyArray(array $array): JoryResponse
    {
        $this->parser = new ArrayParser($array);

        return $this;
    }

    /**
     * Set the response to return the record count.
     *
     * @return $this
     */
    public function count(): JoryResponse
    {
        $this->count = true;

        return $this;
    }

    /**
     * Set the response to return a single model by the given id.
     *
     * @param $modelId
     * @return $this
     */
    public function find($modelId): JoryResponse
    {
        $this->modelId = $modelId;
        $this->first();

        return $this;
    }

    /**
     * Set the response to return only a single model.
     *
     * @return $this
     */
    public function first(): JoryResponse
    {
        $this->first = true;

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     * @return void
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function toResponse($request)
    {
        $data = $this->toArray();

        $responseKey = $this->getDataResponseKey();
        $response = $responseKey === null ? $data : [$responseKey => $data];

        return response($response);
    }

    /**
     * Get the result for the response.
     * (could also be an int instead of an array when using count())
     *
     * @return mixed
     * @throws LaravelJoryException
     * @throws JoryException
     */
    public function toArray()
    {
        $builder = $this->getJoryBuilder();

        $builder->onQuery($this->getBaseQuery());

        if ($this->count) {
            return $builder->getCount();
        }

        if ($this->first) {
            $model = $builder->getFirst();
            if (!$model) {
                return null;
            }

            return $this->joryResource->modelToArray($model);
        }

        $models = $builder->get();

        $result = [];
        foreach ($models as $model) {
            $result[] = $this->joryResource->modelToArray($model);
        }

        return $result;
    }

    /**
     * Get the key on which data should be returned.
     *
     * @return null|string
     */
    protected function getDataResponseKey(): ?string
    {
        return config('jory.response.data-key');
    }

    /**
     * Create and put together a JoryBuilder based
     * on the current settings in the response.
     *
     * @return JoryBuilder
     * @throws LaravelJoryException
     * @throws JoryException
     */
    protected function getJoryBuilder(): JoryBuilder
    {
        if (!$this->joryResource) {
            throw new LaravelJoryException('No resource has been set on the JoryResponse. Use the on() method to set a resource.');
        }

        $this->joryResource->setJory($this->getJory());

        $this->joryResource->validate();

        return app()->makeWith(JoryBuilder::class, ['joryResource' => $this->joryResource]);
    }

    /**
     * Get the base query to apply the jory on.
     *
     * @return mixed
     */
    protected function getBaseQuery(): Builder
    {
        // If there has been given a query explicitly, use this one without further modifying it.
        if ($this->query) {
            return $this->query;
        }

        // Else; create a query from the given model in the registration.
        $modelClass = $this->joryResource->getModelClass();
        $query = $modelClass::query();

        // When a modelId is passed we add a filter to only get this record.
        if ($this->modelId !== null) {
            $query->whereKey($this->modelId);
        }

        return $query;
    }

    /**
     * Get the Jory query object to be applied.
     *
     * @return Jory
     * @throws JoryException
     */
    protected function getJory(): Jory
    {
        /**
         * If a parser is set return the Jory from this parser,
         * otherwise default to the data in the request.
         */
        if ($this->parser) {
            return $this->parser->getJory();
        }

        return (new RequestParser($this->request))->getJory();
    }
}