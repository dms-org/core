<?php declare(strict_types = 1);

namespace Dms\Core\Form\Binding\Accessor;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;

/**
 * The field to property accessor class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldPropertyAccessor extends FieldAccessor
{
    /**
     * @var FinalizedPropertyDefinition
     */
    protected $property;

    /**
     * @param FinalizedClassDefinition $classDefinition
     * @param string                   $propertyName
     *
     * @throws InvalidArgumentException
     * @throws TypeMismatchException
     */
    public function __construct(FinalizedClassDefinition $classDefinition, string $propertyName)
    {
        parent::__construct($classDefinition->getClassName());
        $this->property = $classDefinition->getProperty($propertyName);
    }

    /**
     * @inheritDoc
     */
    protected function getValueFrom($object)
    {
        return $this->ensureAccessible(function ($propertyName) use ($object) {
            return $object->{$propertyName};
        });
    }

    /**
     * @inheritDoc
     */
    protected function bindValueTo($object, $processedFieldValue)
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