<?php

namespace Iddigital\Cms\Core\Model\Type;

/**
 * The array type class.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayType extends WithElementsType
{
    public function __construct(IType $elementType)
    {
        parent::__construct($elementType, 'array<' . $elementType->asTypeString() . '>');
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    protected function intersection(IType $type)
    {
        if ($type instanceof self) {
            $elementType = $this->elementType->intersect($type->elementType);

            return $elementType ? new self($elementType) : null;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value)
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $element) {
            if (!$this->elementType->isOfType($element)) {
                return false;
            }
        }

        return true;
    }
}