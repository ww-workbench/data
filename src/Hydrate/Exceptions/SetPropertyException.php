<?php
declare(strict_types=1);

namespace WebWizardry\Data\Hydrate\Exceptions;

use Throwable;
use WebWizardry\Data\Exception\UnknownPropertyException;

class SetPropertyException extends UnknownPropertyException
{
    public function __construct(
        object $object,
        string $propertyName,
        string $message = null,
        int $code = 0,
        Throwable $previous = null
    ) {
        $message ??= sprintf('Unable to set property "%s" (does not exist in class "%s")',
            $propertyName, basename(get_class($object))
        );

        parent::__construct($object, $propertyName, $message, $code, $previous);
    }
}