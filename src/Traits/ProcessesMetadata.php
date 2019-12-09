<?php


namespace JosKolenberg\LaravelJory\Traits;


use Illuminate\Http\Request;
use JosKolenberg\LaravelJory\Exceptions\LaravelJoryCallException;
use JosKolenberg\LaravelJory\Helpers\CaseManager;

trait ProcessesMetadata
{

    /**
     * @var array
     */
    protected $availableMeta = [];

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * Initialize any requested metadata.
     * @param Request $request
     */
    protected function initMetadata(Request $request): void
    {
        $caseManager = app(CaseManager::class);

        foreach (config('jory.metadata') as $name => $metaClass) {
            $this->availableMeta[$caseManager->toCurrent($name)] = $metaClass;
        }

        $requestedMetaData = $request->input(config('jory.request.meta-key'), []);

        $this->validateRequestedMeta($requestedMetaData);

        foreach ($requestedMetaData as $metaName){
            $this->meta[$metaName] = new $this->availableMeta[$metaName]($request);
        }
    }

    /**
     * @param array $metaTags
     * @throws LaravelJoryCallException
     */
    protected function validateRequestedMeta(array $metaTags): void
    {
        if(!$metaTags){
            return;
        }

        if(config('jory.response.data-key') === null){
            throw new LaravelJoryCallException(['Meta tags are not supported when data is returned in the root.']);
        }

        $unknownMetas = [];
        foreach ($metaTags as $metaTag){
            if(!array_key_exists($metaTag, $this->availableMeta)){
                $unknownMetas[] = 'Meta tag ' . $metaTag . ' is not supported.';
            }
        }

        if($unknownMetas){
            throw new LaravelJoryCallException($unknownMetas);
        }
    }

    /**
     * Get the requested metadata.
     */
    protected function getMetadata(): ?array
    {
        if(count($this->meta) === 0){
            return null;
        }

        $result = [];

        foreach ($this->meta as $metaName => $meta) {
            $result[$metaName] = $meta->get();
        }

        return $result;
    }

    /**
     * Get the key on which metadata should be returned.
     *
     * @return string
     */
    protected function getMetaResponseKey(): string
    {
        return config('jory.response.meta-key');
    }
}