<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\Validator\GreaterThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\GreaterThanValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanOrEqualValidator;
use Dms\Core\Form\Field\Processor\Validator\LessThanValidator;
use Dms\Core\Model\Type\IType;
use Dms\Core\Form\Field\Type\IComparableFieldConstants as Attrs;

/**
 * The comparable field type base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
trait ComparableFieldTypeTrait
{
    /**
     * @inheritDoc
     */
    protected function buildComparisonProcessors()
    {
        $processors = [];

        $inputType = $this->getComparisonType();

        if ($this->has(Attrs::ATTR_MIN)) {
            $processors[] = new GreaterThanOrEqualValidator($inputType, $this->get(Attrs::ATTR_MIN));
        }

        if ($this->has(Attrs::ATTR_GREATER_THAN)) {
            $processors[] = new GreaterThanValidator($inputType, $this->get(Attrs::ATTR_GREATER_THAN));
        }

        if ($this->has(Attrs::ATTR_MAX)) {
            $processors[] = new LessThanOrEqualValidator($inputType, $this->get(Attrs::ATTR_MAX));
        }

        if ($this->has(Attrs::ATTR_LESS_THAN)) {
            $processors[] = new LessThanValidator($inputType, $this->get(Attrs::ATTR_LESS_THAN));
        }

        return $processors;
    }

    /**
     * @return IType
     */
    abstract protected function getComparisonType();

    /**
     * @param string $attribute
     *
     * @return bool
     */
    abstract protected function has($attribute);

    /**
     * @param string $attribute
     *
     * @return mixed
     */
    abstract protected function get($attribute);
}