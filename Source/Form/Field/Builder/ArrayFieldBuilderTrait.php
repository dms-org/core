<?php

namespace Iddigital\Cms\Core\Form\Field\Builder;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\ExactArrayLengthValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MaxArrayLengthValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\MinArrayLengthValidator;
use Iddigital\Cms\Core\Form\Field\Type\ArrayOfType;
use Iddigital\Cms\Core\Model\Type\IType;

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
     * @return IType
     */
    abstract protected function getCurrentProcessedType();

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