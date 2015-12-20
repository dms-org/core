<?php

namespace Dms\Core\Form\Binding\Field;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;

/**
 * The field to property binding class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldPropertyBinding extends FieldBinding
{
    /**
     * @var FinalizedPropertyDefinition
     */
    protected $property;

    /**
     * @param string                   $fieldName
     * @param FinalizedClassDefinition $classDefinition
     * @param string                   $propertyName
     *
     * @throws InvalidArgumentException
     * @throws TypeMismatchException
     */
    public function __construct($fieldName, FinalizedClassDefinition $classDefinition, $propertyName)
    {
        parent::__construct($fieldName, $classDefinition->getClassName());
        $this->property = $classDefinition->getProperty($propertyName);
    }

    /**
     * @inheritDoc
     */
    protected function getFieldValueFrom($object)
    {
        return $this->ensureAccessible(function ($propertyName) use ($object) {
            return $object->{$propertyName};
        });
    }

    /**
     * @inheritDoc
     */
    protected function bindFieldValueTo($object, $processedFieldValue)
    {
        $this->ensureAccessible(function ($propertyName) use ($object, $processedFieldValue) {
            $object->{$propertyName} = $processedFieldValue;
        });
    }

    private function ensureAccessible(callable $callable)
    {
        return call_user_func(
                \Closure::bind(
                        $callable,
                        $this,
                        $this->property->getAccessibility()->getDeclaredClass()
                ),
                $this->property->getName()
        );
    }
}