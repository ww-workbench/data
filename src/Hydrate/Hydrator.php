<?php
declare(strict_types=1);

namespace WebWizardry\Data\Hydrate;
use Closure;
use ReflectionClass;
use ReflectionException;
use WebWizardry\Data\Hydrate\Exceptions\GetPropertyException;
use WebWizardry\Data\Hydrate\Exceptions\SetPropertyException;

final class Hydrator
{
    const string EXTRA_PROPERTIES_PARAM_NAME = '__extraProperties';

    public static function set(object $object, string $propertyName, mixed $propertyValue): void
    {
        self::hydrate($object, [$propertyName => $propertyValue]);
    }

    public static function get(object $object, string $propertyName): mixed
    {
        return self::extract($object, $propertyName);
    }

    public static function hydrate(object $object, array $properties): void
    {
        $writer = function & ($object, $properties) {
            return Closure::bind(function & () use ($properties) {
                foreach ($properties as $propertyName => $propertyValue) {

                    $setter = 'set' . $propertyName;
                    if (method_exists($this, $setter)) {
                        $this->$setter($propertyValue);
                    } elseif (property_exists($this, $propertyName)) {
                        $this->$propertyName = $propertyValue;
                    } elseif (property_exists($this, '__extraProperties')) {
                        $this->__extraProperties[$propertyName] = $propertyValue;
                    } else {
                        throw new SetPropertyException($this, $propertyName);
                    }

                }
            }, $object, $object)->__invoke();
        };

        $writer($object, $properties);
    }

    public static function extract(object $object, string|array $properties): mixed
    {
        $reader = function & ($object, $properties) {
            return Closure::bind(function & () use ($properties) {

                $requested = null;
                if (is_string($properties)) {
                    $requested = $properties;
                    $properties = [$properties];
                }

                $value = [];

                foreach ($properties as $propertyName) {
                    $getter = 'get' . $propertyName;
                    if (method_exists($this, $getter)) {
                        $value[$propertyName] = $this->$getter($properties);
                    } elseif (property_exists($this, $propertyName)) {
                        $value[$propertyName] = $this->$propertyName;
                    } elseif (
                        property_exists($this, '__extraProperties')
                        && array_key_exists($propertyName, $this->__extraProperties)
                    ) {
                        $value[$propertyName] =  $this->__extraProperties[$propertyName];
                    } else {
                        throw new GetPropertyException($this, $propertyName);
                    }
                }

                return $requested ? $value[$requested] : $value;

            }, $object, $object)->__invoke();
        };

        return $reader($object, $properties);
    }

    /**
     * @throws ReflectionException
     */
    public static function make(string $class, array $properties = [], array $constructorArguments = []): mixed
    {
        $object = empty($constructorArguments)
            ? new $class()
            : (new ReflectionClass($class))->newInstanceArgs($constructorArguments);

        self::hydrate($object, $properties);
        return $object;
    }
}