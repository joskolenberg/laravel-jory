<?php


namespace JosKolenberg\LaravelJory\Register;


use Illuminate\Support\Collection;

interface RegistrarInterface
{

    /**
     * Get all registered registrations.
     *
     * @return Collection
     */
    public function getRegistrations(): Collection;

}