<?php
declare(strict_types=1);

namespace WebWizardry\Data\Exception;

use Exception;

class UnknownPropertyException extends Exception
{
    public function __construct(
        private readonly object $object,
        private readonly string $propertyName,
        string $message = "",
        int $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}