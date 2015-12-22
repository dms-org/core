<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\ArrayAllProcessor;
use Dms\Core\Form\Field\Processor\EntityArrayLoaderProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\EntityIdArrayValidator;
use Dms\Core\Form\Field\Processor\Validator\IntValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The array type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfEntityIdsType extends ArrayOfType
{
    const ATTR_ELEMENT_TYPE = 'element-type';
    const ATTR_MIN_ELEMENTS = 'min-elements';
    const ATTR_MAX_ELEMENTS = 'max-elements';

    /**
     * @var IEntitySet
     */
    private $entities;

    /**
     * @var bool
     */
    private $loadAsObjects;

    /**
     * ArrayOfEntityIdsType constructor.
     *
     * @param IEntitySet $entities
     * @param IField     $entityIdField
     * @param bool       $loadAsObjects
     */
    public function __construct(IEntitySet $entities, IField $entityIdField, $loadAsObjects = false)
    {
        $this->entities      = $entities;
        $this->loadAsObjects = $loadAsObjects;
        parent::__construct($entityIdField);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        $processors = [];

        $this->buildArrayLengthValidators($processors);

        $processors[] = new ArrayAllProcessor([
                new IntValidator($this->getElementType()->getPhpTypeOfInput()),
                new TypeProcessor('int'),
                new RequiredValidator(Type::int()->nullable())
        ]);

        $processors[] = new EntityIdArrayValidator(Type::arrayOf(Type::int())->nullable(), $this->entities);

        if ($this->loadAsObjects) {
            $processors[] = new EntityArrayLoaderProcessor($this->entities);
        }

        $this->buildArrayElementsValidators($processors);

        return $processors;
    }
}