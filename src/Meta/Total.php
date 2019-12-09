<?php


namespace JosKolenberg\LaravelJory\Meta;


use Illuminate\Support\Facades\Route;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Helpers\ResourceNameHelper;

class Total extends Metadata
{

    /**
     * Get the return value for the metadata.
     * Called at the end of the request.
     *
     * @return mixed
     */
    public function get()
    {
        $route = Route::currentRouteName();

        if($route === 'jory.get'){
            return $this->getIndexCount();
        }

        if($route === 'jory.multiple'){
            return $this->getMultipleCount();
        }

        return null;
    }

    /**
     * Get the total record count (including filtering) for an index request.
     *
     * @return mixed
     */
    protected function getIndexCount(): int
    {
        $resource = $this->request->route('resource');

        return Jory::byUri($resource)->count()->toArray();
    }

    /**
     * Get the total record count (including filtering) for
     * all items in a request for multiple resources.
     *
     * @return array
     */
    protected function getMultipleCount(): array
    {
        $data =  $this->request->input(config('jory.request.key'), '{}');

        $result = [];

        foreach ($data as $resourceName => $jory) {
            $resource = ResourceNameHelper::explode($resourceName);

            if($resource->type === 'multiple'){
                $result[$resource->alias] = Jory::byUri($resource->baseName)->applyArray($jory)->count()->toArray();
            }
        }

        return $result;
    }
}