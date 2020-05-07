<?php


namespace JosKolenberg\LaravelJory\Traits;


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
     * When an asterisk is present in the array, we will add all the configured fields.
     *
     * @param Jory $jory
     * @param array $fields
     */
    protected function applyFieldsToJory(Jory $jory, array $fields): void
    {
        if($jory->getFields() === null){
            $jory->setFields([]);
        }

        if (in_array('*', $jory->getFields())) {
            $allFields = [];
            foreach ($fields as $field) {
                $allFields[] = $field->getField();
            }
            $jory->setFields($allFields);
        }
    }

    /**
     * Apply the sort settings in this Config on the Jory query.
     *
     * @param Jory $jory
     * @param array $sorts
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
                $defaultSorts[$sort->getDefaultIndex()] = new \JosKolenberg\Jory\Support\Sort($sort->getName(),
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