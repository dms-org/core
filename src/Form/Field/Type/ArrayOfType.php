<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\ArrayAllProcessor;
use Dms\Core\Form\Field\Processor\Validator\ArrayUniqueValidator;
use Dms\Core\Form\Field\Processor\Validator\ExactArrayLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\MaxArrayLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\MinArrayLengthValidator;
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
    const ATTR_EXACT_ELEMENTS = 'exact-elements';

    const ATTR_UNIQUE_ELEMENTS = 'unique-elements';

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
        $processors = [];

        $this->buildArrayLengthValidators($processors);

        if (count($this->elementField->getProcessors()) > 0) {
            $processors[] = new ArrayAllProcessor(
                    $this->elementField->getProcessors(),
                    $this->getElementType()->getProcessedPhpType()
            );
        }

        $this->buildArrayElementsValidators($processors);

        return $processors;
    }

    /**
     * @param array $processors
     *
     * @return void
     */
    protected function buildArrayLengthValidators(array &$processors)
    {
        $inputType = Type::arrayOf(Type::mixed())->nullable();

        if ($this->has(self::ATTR_MIN_ELEMENTS)) {
            $processors[] = new MinArrayLengthValidator($inputType, $this->get(self::ATTR_MIN_ELEMENTS));
        }

        if ($this->has(self::ATTR_MAX_ELEMENTS)) {
            $processors[] = new MaxArrayLengthValidator($inputType, $this->get(self::ATTR_MAX_ELEMENTS));
        }

        if ($this->has(self::ATTR_EXACT_ELEMENTS)) {
            $processors[] = new ExactArrayLengthValidator($inputType, $this->get(self::ATTR_EXACT_ELEMENTS));
        }
    }

    /**
     * @param array $processors
     *
     * @return void
     */
    protected function buildArrayElementsValidators(array &$processors)
    {
        $inputType = Type::arrayOf($this->getElementType()->getProcessedPhpType())->nullable();

        if ($this->get(self::ATTR_UNIQUE_ELEMENTS)) {
            $processors[] = new ArrayUniqueValidator($inputType);
        }
    }
}