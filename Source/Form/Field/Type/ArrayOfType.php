<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\ArrayAllProcessor;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IFieldType;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The array type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfType extends FieldType
{
    const ATTR_ELEMENT_TYPE = 'element-type';
    const ATTR_MIN_ELEMENTS = 'min-elements';
    const ATTR_MAX_ELEMENTS = 'max-elements';
    /**
     * @var IField
     */
    private $elementField;

    public function __construct(IField $elementField)
    {
        $this->attributes[self::ATTR_ELEMENT_TYPE] = $elementField->getType();
        $this->elementField                        = $elementField;
        parent::__construct();
    }

    /**
     * @return IFieldType
     */
    public function getElementType()
    {
        return $this->get(self::ATTR_ELEMENT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function buildPhpTypeOfInput()
    {
        return Type::arrayOf($this->getElementType()->getPhpTypeOfInput());
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
                new ArrayAllProcessor($this->elementField->getProcessors())
        ];
    }
}