<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Processor\ArrayAllProcessor;
use Dms\Core\Form\Field\Processor\ObjectArrayLoaderProcessor;
use Dms\Core\Form\Field\Processor\Validator\ObjectIdArrayValidator;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IFieldType;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The array of object id type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfObjectIdsType extends ArrayOfType
{
    /**
     * @var IIdentifiableObjectSet
     */
    private $objects;

    /**
     * @var bool
     */
    private $loadAsObjects;

    /**
     * ArrayOfObjectIdsType constructor.
     *
     * @param IIdentifiableObjectSet $entities
     * @param IField                 $entityIdField
     * @param bool                   $loadAsObjects
     */
    public function __construct(IIdentifiableObjectSet $entities, IField $entityIdField, bool $loadAsObjects = false)
    {
        $this->objects       = $entities;
        $this->loadAsObjects = $loadAsObjects;
        parent::__construct($entityIdField);
    }

    /**
     * @param IFieldType $objectIdFieldType
     *
     * @return static
     */
    public function withElementFieldType(IFieldType $objectIdFieldType)
    {
        $clone               = clone $this;
        $clone->elementField = Field::element()->type($objectIdFieldType)->build();
        $clone->initializeFromCurrentAttributes();

        return $clone;
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors() : array
    {
        $processors = [];

        $this->buildArrayLengthValidators($processors);

        $processors[] = new ArrayAllProcessor(Field::element()->int()->required()->build());

        $processors[] = new ObjectIdArrayValidator(Type::arrayOf(Type::int())->nullable(), $this->objects);

        if ($this->loadAsObjects) {
            $processors[] = new ObjectArrayLoaderProcessor($this->objects);
            $elementType  = $this->objects->getElementType();
        } else {
            $elementType = Type::int();
        }

        $this->buildArrayElementsValidators($processors, $elementType);

        return $processors;
    }
}