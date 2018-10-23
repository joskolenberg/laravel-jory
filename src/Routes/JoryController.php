<?php

namespace JosKolenberg\LaravelJory\Routes;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JoryController extends Controller
{
    public function index($uri, Request $request)
    {
        $modelClass = config('jory.routes.'.$uri);

        if (! $modelClass) {
            abort(404);
        }

        return $modelClass::jory()->applyRequest($request);
    }

    public function show($uri, $id, Request $request)
    {
        $modelClass = config('jory.routes.'.$uri);

        if (! $modelClass) {
            abort(404);
        }

        $model = $modelClass::findOrFail($id);

        return $modelClass::jory()->applyRequest($request)->onModel($model);
    }
}