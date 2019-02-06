<?php

namespace JosKolenberg\LaravelJory\Traits;

use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Support\Sort;
use JosKolenberg\LaravelJory\Config\Config;

/**
 * Trait HandlesJoryBuilderConfiguration.
 */
trait HandlesJoryBuilderConfiguration
{
    /**
     * @var Config|null
     */
    protected $config = null;

    /**
     * Initialize the config object.
     *
     * @param string $modelClass
     */
    protected function initConfig(string $modelClass): void
    {
        // Create the config based on the settings in config()
        $this->config = new Config($modelClass);
        $this->config($this->config);
    }

    /**
     * Get the Config.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Apply the settings in the Config on the Jory.
     *
     * When no fields are specified in the request, the default fields in Config will be set on the Jory.
     *
     * @param \JosKolenberg\LaravelJory\Config\Config $config
     * @param \JosKolenberg\Jory\Jory $jory
     * @return void
     * @throws \JosKolenberg\Jory\Exceptions\JoryException
     */
    public function applyConfigToJory(Config $config, Jory $jory): void
    {
        if ($jory->getFields() === null && $config->getFields() !== null) {
            // No fields set in the request, but there are fields
            // specified in the config, than we will update the fields
            // with the ones to be shown by default.
            $defaultFields = [];
            foreach ($config->getFields() as $field) {
                if ($field->isShownByDefault()) {
                    $defaultFields[] = $field->getField();
                }
            }
            $jory->setFields($defaultFields);
        }

        if ($config->getSorts() !== null) {
            // When default sorts are defined, add them to the Jory
            // When no sorts are requested, the default sorts in the builder will be applied.
            // When sorts are requested, the default sorts are applied after the requested ones.
            $defaultSorts = [];
            foreach ($config->getSorts() as $sort) {
                if ($sort->getDefaultIndex() !== null) {
                    $defaultSorts[$sort->getDefaultIndex()] = new Sort($sort->getField(), $sort->getDefaultOrder());
                }
            }
            ksort($defaultSorts);
            foreach ($defaultSorts as $sort) {
                $jory->addSort($sort);
            }
        }

    }

    /**
     * Create the config for this builder.
     *
     * This config will be used to:
     *      - Show the options for the resource when using the OPTIONS http method
     *      - Fields:
     *          - Validate if the requested fields are available.
     *          - Update the Jory's fields attribute with the ones marked to be shown by default
     *              when no particular fields are requested.
     *
     * @param Config $config
     */
    protected function config(Config $config): void
    {
    }
}