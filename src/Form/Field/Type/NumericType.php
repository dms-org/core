<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\IType;

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
    protected function buildProcessors() : array
    {
        return array_merge(parent::buildProcessors(), $this->buildComparisonProcessors());
    }

    /**
     * @inheritDoc
     */
    protected function getComparisonType() : IType
    {
        return $this->getProcessedScalarType();
    }
}