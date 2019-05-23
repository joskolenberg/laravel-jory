<?php


namespace JosKolenberg\LaravelJory\Register;


class ManualRegistrar implements RegistrarInterface
{
    /**
     * @var array
     */
    protected $registrations = [];

    /**
     * Add a registration.
     *
     * @param \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration $registration
     * @return \JosKolenberg\LaravelJory\Register\JoryBuilderRegistration
     */
    public function add(JoryBuilderRegistration $registration): ? JoryBuilderRegistration
    {
        // There can be only one registration for a model
        // When a second one is registered for the same model remove the previous
        foreach ($this->registrations as $key => $existingRegistration) {
            if ($registration->getModelClass() == $existingRegistration->getModelClass()) {
                unset($this->registrations[$key]);
            }
        }

        $this->registrations[] = $registration;

        return $registration;
    }

    /**
     * Get all registered registrations
     *
     * @return array
     */
    public function getRegistrations(): array
    {
        return $this->registrations;
    }
}