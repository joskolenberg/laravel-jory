<?php

namespace JosKolenberg\LaravelJory\Http\Controllers;

use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

class JoryController extends Controller
{
    /**
     * Load a collection for a single resource.
     */
    public function index(string $resource)
    {
        return Jory::byUri($resource);
    }

    /**
     * Count the number of items in a resource.
     */
    public function count(string $resource)
    {
        return Jory::byUri($resource)->count();
    }

    /**
     * Give a single record.
     */
    public function show(string $resource, $id)
    {
        return Jory::byUri($resource)->find($id);
    }

    /**
     * Load multiple resources at once.
     *
     */
    public function multiple()
    {
        return Jory::multiple();
    }

    /**
     * Give the options for a resource.
     */
    public function options(string $resource, JoryResourcesRegister $register)
    {
        $joryResource = $register->getByUri($resource);

        return response($joryResource->getConfig()->toArray());
    }

    /**
     * Display a list of available resources.
     */
    public function resourceList(JoryResourcesRegister $register)
    {
        return response(['resources' => $register->getUrisArray()]);
    }
}
