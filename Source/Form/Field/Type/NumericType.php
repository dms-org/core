<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\IFieldProcessor;

/**
 * The numeric field type base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class NumericType extends ScalarType implements IComparableFieldConstants
{
    use ComparableFieldTypeTrait;

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return array_merge(parent::buildProcessors(), $this->buildComparisonProcessors());
    }

    /**
     * @inheritDoc
     */
    protected function getComparisonType()
    {
        return $this->getProcessedScalarType();
    }
}