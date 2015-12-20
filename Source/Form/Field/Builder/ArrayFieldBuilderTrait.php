<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\AllUniquePropertyValidator;
use Dms\Core\Form\Field\Processor\Validator\ExactArrayLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\MaxArrayLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\MinArrayLengthValidator;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Type\ArrayType;

/**
 * The array field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
trait ArrayFieldBuilderTrait
{
    /**
     * Validates the array has at least the supplied amount of elements.
     *
     * @param int $length
     *
     * @return static
     */
    public function minLength($length)
    {
        return $this
                ->attr(ArrayOfType::ATTR_MIN_ELEMENTS, $length)
                ->validate(new MinArrayLengthValidator($this->getCurrentProcessedType(), $length));
    }

    /**
     * Validates the array has at most the supplied amount of elements.
     *
     * @param int $length
     *
     * @return static
     */
    public function maxLength($length)
    {
        return $this
                ->attr(ArrayOfType::ATTR_MAX_ELEMENTS, $length)
                ->validate(new MaxArrayLengthValidator($this->getCurrentProcessedType(), $length));
    }

    /**
     * Validates the array has the supplied amount of elements.
     *
     * @param int $length
     *
     * @return static
     */
    public function exactLength($length)
    {
        return $this
                ->attr(ArrayOfType::ATTR_MIN_ELEMENTS, $length)
                ->attr(ArrayOfType::ATTR_MAX_ELEMENTS, $length)
                ->validate(new ExactArrayLengthValidator($this->getCurrentProcessedType(), $length));
    }

    /**
     * Validates that all the array elements are unique within the supplied
     * set of object properties.
     *
     * @param IObjectSet $objects
     * @param string     $propertyName
     *
     * @return static
     */
    public function allUniqueIn(IObjectSet $objects, $propertyName)
    {
        return $this
                ->validate(new AllUniquePropertyValidator($this->getCurrentProcessedType(), $objects, $propertyName));
    }

    /**
     * @param string $function
     *
     * @return ArrayType
     */
    abstract protected function getCurrentProcessedType($function = __FUNCTION__);

    /**
     * @param $name
     * @param $value
     *
     * @return static
     */
    abstract protected function attr($name, $value);

    /**
     * @param FieldValidator $validator
     *
     * @return static
     */
    abstract protected function validate(FieldValidator $validator);
}