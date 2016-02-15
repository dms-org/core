<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\AllUniquePropertyValidator;
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
    public function minLength(int $length)
    {
        return $this->attr(ArrayOfType::ATTR_MIN_ELEMENTS, $length);
    }

    /**
     * Validates the array has at most the supplied amount of elements.
     *
     * @param int $length
     *
     * @return static
     */
    public function maxLength(int $length)
    {
        return $this->attr(ArrayOfType::ATTR_MAX_ELEMENTS, $length);
    }

    /**
     * Validates the array has the supplied amount of elements.
     *
     * @param int $length
     *
     * @return static
     */
    public function exactLength(int $length)
    {
        return $this->attr(ArrayOfType::ATTR_EXACT_ELEMENTS, $length);
    }

    /**
     * Validates that all the array elements are unique.
     *
     * @return static
     */
    public function containsNoDuplicates()
    {
        return $this->attr(ArrayOfType::ATTR_UNIQUE_ELEMENTS, true);
    }

    /**
     * Validates that all the array elements are unique within the supplied
     * set of object properties.
     *
     * @param IObjectSet $objects
     * @param string     $propertyName the property member expression
     *
     * @return static
     */
    public function allUniqueIn(IObjectSet $objects, string $propertyName)
    {
        return $this
                ->validate(new AllUniquePropertyValidator($this->getCurrentProcessedType(__FUNCTION__), $objects, $propertyName));
    }

    /**
     * @param string $function
     *
     * @return ArrayType
     */
    abstract protected function getCurrentProcessedType(string $function = __FUNCTION__) : \Dms\Core\Model\Type\IType;

    /**
     * @param $name
     * @param $value
     *
     * @return static
     */
    abstract protected function attr(string $name, $value);

    /**
     * @param FieldValidator $validator
     *
     * @return static
     */
    abstract protected function validate(FieldValidator $validator);
}