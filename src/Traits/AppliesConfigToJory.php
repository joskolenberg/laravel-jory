<?php


namespace JosKolenberg\LaravelJory\Traits;


use JosKolenberg\Jory\Exceptions\JoryException;
use JosKolenberg\Jory\Jory;
use JosKolenberg\LaravelJory\Config\Config;

trait AppliesConfigToJory
{

    /**
     * Update a Jory query object with the defaults from a Config object.
     *
     * @param Jory $jory
     * @param Config $config
     * @return Jory
     * @throws JoryException
     */
    protected function applyConfigToJory(Jory $jory, Config $config): Jory
    {
        $this->applyFieldsToJory($jory, $config->getFields());
        $this->applySortsToJory($jory, $config->getSorts());
        $this->applyOffsetAndLimitToJory($jory, $jory->getLimit(), $config->getLimitDefault());

        return $jory;
    }

    /**
     * Apply the field settings in this Config on the Jory query.
     *
     * When no fields are specified in the request, the default fields in will be set on the Jory query.
     *
     * @param Jory $jory
     * @param array $fields
     */
    protected function applyFieldsToJory(Jory $jory, array $fields): void
    {
        if ($jory->getFields() === null) {
            // No fields set in the request, than we will update the fields
            // with the ones to be shown by default.
            $defaultFields = [];
            foreach ($fields as $field) {
                if ($field->isShownByDefault()) {
                    $defaultFields[] = $field->getField();
                }
            }
            $jory->setFields($defaultFields);
        }
    }

    /**
     * Apply the sort settings in this Config on the Jory query.
     *
     * @param Jory $jory
     * @param array $sorts
     * @throws JoryException
     */
    protected function applySortsToJory(Jory $jory, array $sorts): void
    {
        /**
         * When default sorts are defined, add them to the Jory query.
         * When no sorts are requested, the default sorts in this Config will be applied.
         * When sorts are requested, the default sorts are applied after the requested ones.
         */
        $defaultSorts = [];
        foreach ($sorts as $sort) {
            if ($sort->getDefaultIndex() !== null) {
                $defaultSorts[$sort->getDefaultIndex()] = new \JosKolenberg\Jory\Support\Sort($sort->getField(),
                    $sort->getDefaultOrder());
            }
        }
        ksort($defaultSorts);
        foreach ($defaultSorts as $sort) {
            $jory->addSort($sort);
        }
    }

    /**
     * Apply the offset and limit settings in this Config on the Jory query.
     *
     * When no offset or limit is set, the defaults will be used.
     *
     * @param Jory $jory
     * @param int|null $limit
     * @param int|null $limitDefault
     */
    protected function applyOffsetAndLimitToJory(Jory $jory, int $limit = null, int $limitDefault = null): void
    {
        if (is_null($limit) && $limitDefault !== null) {
            $jory->setLimit($limitDefault);
        }
    }
}