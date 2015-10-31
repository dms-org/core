<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedObject;

/**
 * The label object strategy definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LabelObjectStrategyDefiner
{
    /**
     * @var string
     */
    private $classType;

    /**
     * @var callable
     */
    private $callback;

    /**
     * LabelObjectStrategyDefiner constructor.
     *
     * @param string   $classType
     * @param callable $callback
     */
    public function __construct($classType, callable $callback)
    {
        $this->classType = $classType;
        $this->callback  = $callback;
    }

    /**
     * Labels the object with the value of the supplied property.
     *
     * @param string $propertyName
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function fromProperty($propertyName)
    {
        if (!property_exists($this->classType, $propertyName)) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: property %s::$%s does not exist',
                    __METHOD__, $this->classType, $propertyName
            );
        }

        $this->fromCallback(function (ITypedObject $object) use ($propertyName) {
            return $object->toArray()[$propertyName];
        });
    }

    /**
     * Labels the object with the returned value of the supplied callback.
     *
     * Example:
     * <code>
     * ->fromCallback(function (Person $person) {
     *      return $person->getFullName();
     * });
     * </code>
     *
     * @param callable $labelObjectCallback
     *
     * @return void
     */
    public function fromCallback(callable $labelObjectCallback)
    {
        call_user_func($this->callback, $labelObjectCallback);
    }
}