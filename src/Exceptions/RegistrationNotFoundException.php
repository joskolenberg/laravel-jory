<?php


namespace JosKolenberg\LaravelJory\Exceptions;

/**
 * Class RegistrationNotFoundException
 *
 * Exception to be thrown when no registration is found for a ModelClass.
 *
 * This exception usually shows a bug into the implementation
 * and will not be caught by the JoryHandler.
 */
class RegistrationNotFoundException extends \Exception
{

}