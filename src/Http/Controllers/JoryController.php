<?php

namespace JosKolenberg\LaravelJory\Http\Controllers;

use Illuminate\Routing\Controller;
use JosKolenberg\LaravelJory\Facades\Jory;
use JosKolenberg\LaravelJory\Register\JoryBuildersRegister;

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
    public function options(string $resource, JoryBuildersRegister $register)
    {
        $registration = $register->getByUri($resource);

        $joryBuilderClass = $registration->getBuilderClass();

        return (new $joryBuilderClass())->getConfig();
    }

    /**
     * Display a list of available resources.
     */
    public function resourceList(JoryBuildersRegister $register)
    {
        return response(['resources' => $register->getUrisArray()]);
    }
}
