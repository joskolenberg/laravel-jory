<?php


namespace JosKolenberg\LaravelJory\Traits;


use Illuminate\Http\Request;
use JosKolenberg\LaravelJory\Helpers\CaseManager;

trait ProcessesMetadata
{

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

        $availableMetaData = [];
        foreach (config('jory.metadata') as $name => $metaClass) {
            $availableMetaData[$caseManager->toCurrent($name)] = $metaClass;
        }

        $requestedMetaData = $request->input(config('jory.request.meta-key'), []);

        foreach ($availableMetaData as $metaName => $metaClass) {
            if (in_array($metaName, $requestedMetaData)) {
                $metaObject = new $metaClass();
                $metaObject->init();
                $this->meta[$metaName] = $metaObject;
            }
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