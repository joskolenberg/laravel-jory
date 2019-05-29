<?php


namespace JosKolenberg\LaravelJory\Register;


use Illuminate\Support\Collection;

interface RegistersJoryResources
{

    /**
     * Get all registered registrations.
     *
     * @return Collection
     */
    public function getJoryResources(): Collection;

}