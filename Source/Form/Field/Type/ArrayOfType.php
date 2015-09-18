<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\Form\Field\Processor\ArrayAllProcessor;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Form\IFieldType;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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