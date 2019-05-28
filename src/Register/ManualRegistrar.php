<?php


namespace JosKolenberg\LaravelJory\Register;


use Illuminate\Support\Collection;

class ManualRegistrar implements RegistersJoryBuilders
{
    /**
     * @var array
     */
    protected $registrations;

    public function __construct()
    {
        $this->registrations = new Collection();
    }

    /**
     * Add a registration.
     *
     * @param JoryBuilderRegistration $registration
     * @return JoryBuilderRegistration
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

        $this->registrations->push($registration);

        return $registration;
    }

    /**
     * Get all registered registrations.
     *
     * @return Collection
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }
}