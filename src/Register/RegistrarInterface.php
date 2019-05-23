<?php


namespace JosKolenberg\LaravelJory\Register;


interface RegistrarInterface
{

    /**
     * Get all registered registrations
     *
     * @return array
     */
    public function getRegistrations(): array;

}