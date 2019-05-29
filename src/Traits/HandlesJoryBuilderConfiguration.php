<?php

namespace JosKolenberg\LaravelJory\Traits;

use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\Jory\Jory;
use JosKolenberg\Jory\Support\Sort;
use JosKolenberg\LaravelJory\Config\Config;
use JosKolenberg\LaravelJory\Register\JoryResourcesRegister;

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
     */
    protected function initConfig(): void
    {
        // Create the config based on the settings in config()
        $modelClass = app()->make(JoryResourcesRegister::class)->getByBuilderClass(static::class)->getModelClass();
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
     * @param Config $config
     * @param Jory $jory
     * @return Jory
     * @throws JoryException
     */
    public function applyConfigToJory(Config $config, Jory $jory): Jory
    {
        $this->applyFieldsConfigToJory($config, $jory);
        $this->applySortsConfigToJory($config, $jory);
        $this->applyOffsetAndLimitConfigToJory($config, $jory);

        return $jory;
    }

    /**
     * Apply the field settings in the Config on the Jory.
     *
     * When no fields are specified in the request, the default fields in Config will be set on the Jory.
     *
     * @param Config $config
     * @param Jory $jory
     */
    protected function applyFieldsConfigToJory(Config $config, Jory $jory): void
    {
        if ($jory->getFields() === null) {
            // No fields set in the request, than we will update the fields
            // with the ones to be shown by default.
            $defaultFields = [];
            foreach ($config->getFields() as $field) {
                if ($field->isShownByDefault()) {
                    $defaultFields[] = $field->getField();
                }
            }
            $jory->setFields($defaultFields);
        }
    }

    /**
     * Apply the sort settings in the Config on the Jory.
     *
     * @param Config $config
     * @param Jory $jory
     * @throws JoryException
     */
    protected function applySortsConfigToJory(Config $config, Jory $jory): void
    {
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

    /**
     * Apply the sort settings in the Config on the Jory.
     *
     * @param Config $config
     * @param Jory $jory
     */
    protected function applyOffsetAndLimitConfigToJory(Config $config, Jory $jory): void
    {
        if (is_null($jory->getLimit()) && $config->getLimitDefault() !== null) {
            $jory->setLimit($config->getLimitDefault());
        }
    }

    /**
     * Configure the JoryBuilder.
     *
     * @param Config $config
     */
    abstract protected function config(Config $config): void;
}