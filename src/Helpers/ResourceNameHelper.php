<?php


namespace JosKolenberg\LaravelJory\Helpers;


use stdClass;

class ResourceNameHelper
{

    /**
     * Cut the jory's resource name into logical pieces.
     *
     * A resource name can be the key of a resource when calling
     * for multiple resources which can old parameters and
     * aliases (e.g. "band:count as band_count") or the
     * name of a relation which can only hold an
     * alias (e.g. "songs as favorites").
     *
     * @param $resourceName
     * @return stdClass
     */
    public static function explode($resourceName): \stdClass
    {
        /**
         * First cut the alias part
         */
        $nameParts = explode(' as ', $resourceName);
        if (count($nameParts) === 1) {
            $baseName = $nameParts[0];
            $alias = $nameParts[0];
        } else {
            $baseName = $nameParts[0];
            $alias = $nameParts[1];
        }

        /**
         * Next, check the type of request.
         */
        $nameParts = explode(':', $baseName);
        if (count($nameParts) === 1) {
            $type = 'multiple';
            $id = null;
        } elseif ($nameParts[1] === 'count') {
            $type = 'count';
            $baseName = $nameParts[0];
            $id = null;
        } elseif ($nameParts[1] === 'first') {
            $type = 'first';
            $baseName = $nameParts[0];
            $id = null;
        } else {
            $type = 'find';
            $baseName = $nameParts[0];
            $id = $nameParts[1];
        }

        /**
         * Create the value object.
         */
        $result = new stdClass();
        $result->baseName = $baseName;
        $result->alias = $alias;
        $result->type = $type;
        $result->id = $id;

        return $result;
    }

}