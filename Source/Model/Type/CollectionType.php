<?php

namespace Iddigital\Cms\Core\Model\Type;
use Iddigital\Cms\Core\Model\ITypedCollection;

/**
 * The collection type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CollectionType extends WithElementsType
{
    public function __construct(IType $elementType)
    {
        parent::__construct($elementType, ITypedCollection::class . '<' . $elementType->asTypeString() . '>');
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
        if (!($value instanceof ITypedCollection)) {
            return false;
        }

        return $value->getElementType()->equals($this->elementType);
    }
}