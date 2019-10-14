<?php

namespace JosKolenberg\LaravelJory\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JosKolenberg\LaravelJory\Config\Field;
use JosKolenberg\LaravelJory\JoryResource;

trait HandlesJorySelects
{
    /**
     * Apply the select part of the query.
     *
     * @param $query
     * @param \JosKolenberg\LaravelJory\JoryResource $joryResource
     */
    protected function applySelects($query, JoryResource $joryResource): void
    {
        if (! $joryResource->getConfig()->hasExplicitSelect()) {
            $this->applyDefaultSelect($query);

            return;
        }

        $this->applySelectsByJory($query, $joryResource);
    }

    /**
     * Apply the default way of selecting columns.
     *
     * @param $query
     */
    protected function applyDefaultSelect($query)
    {
        $table = $query->getModel()->getTable();
        $query->select($table.'.*');
    }

    /**
     * Apply the select part of the query based on the requested fields.
     *
     * @param $query
     * @param \JosKolenberg\LaravelJory\JoryResource $joryResource
     */
    protected function applySelectsByJory($query, JoryResource $joryResource)
    {
        $fields = [];

        $table = $query->getModel()->getTable();
        $configuredFields = $joryResource->getConfig()->getFields();

        foreach ($joryResource->getJory()->getFields() as $fieldName) {
            $configuredField = Arr::first($configuredFields, function (Field $configuredField) use ($fieldName) {
                return $configuredField->getField() === $fieldName;
            });

            if ($configuredField->getSelect() === null) {
                $fields[] = $table.'.'. Str::snake($fieldName);
            } else {
                $fields = array_merge($fields, $configuredField->getSelect());
            }
        }

        $query->select($fields);
    }
}