<?php

namespace JosKolenberg\LaravelJory\Blueprint;

use JosKolenberg\Jory\Jory;

/**
 * Class BlueprintApplier
 * 
 * Class which applies a Blueprint to an existing Jory object.
 *
 * @package JosKolenberg\LaravelJory\Blueprint
 */
class BlueprintApplier
{
    /**
     * @var \JosKolenberg\LaravelJory\Blueprint\Blueprint
     */
    protected $blueprint;

    /**
     * @var \JosKolenberg\Jory\Jory
     */
    protected $jory;

    /**
     * Applier constructor.
     *
     * @param Blueprint $blueprint
     * @param Jory $jory
     */
    public function __construct(Blueprint $blueprint, Jory $jory)
    {
        $this->blueprint = $blueprint;
        $this->jory = $jory;
    }

    /**
     * Apply the blueprint to the Jory object
     */
    public function apply(): void
    {
        $this->applyFields();
    }

    /**
     * Apply the default fields inthe blueprint to the jory object
     * if no fields are specified in the Jory object.
     */
    public function applyFields()
    {
        if($this->jory->getFields() === null && $this->blueprint->getFields() !== null){
            // No fields set on the jory and fields are specified in blueprint
            // than we will update the fields with the ones to be shown by default.
            $defaultFields = [];
            foreach ($this->blueprint->getFields() as $field){
                if($field->isShownByDefault()){
                    $defaultFields[] = $field->getField();
                }
                $this->jory->setFields($defaultFields);
            }
        }
    }
}