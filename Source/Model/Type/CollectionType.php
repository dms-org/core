<?php

namespace Iddigital\Cms\Core\Model\Type;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedCollection;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * The collection type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CollectionType extends WithElementsType
{
    /**
     * @var string
     */
    private $collectionClass;

    public function __construct(IType $elementType, $collectionClass = ITypedCollection::class)
    {
        if (!is_a($collectionClass, ITypedCollection::class, true)) {
            throw InvalidArgumentException::format(
                    'Invalid collection class supplied to %s: expecting type compatible with %s, %s given',
                    __METHOD__, ITypedCollection::class, $collectionClass
            );
        }

        parent::__construct($elementType, $collectionClass . '<' . $elementType->asTypeString() . '>');

        $this->collectionClass = $collectionClass;
    }

    /**
     * @return string
     */
    public function getCollectionClass()
    {
        return $this->collectionClass;
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    protected function intersection(IType $type)
    {
        if ($type instanceof self) {
            $elementType     = $this->elementType->intersect($type->elementType);
            $collectionClass = Type::object($this->collectionClass)
                    ->intersect(Type::object($type->collectionClass))
                    ->getClass();

            return $elementType && $collectionClass ? new self($elementType, $collectionClass) : null;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value)
    {
        if (!($value instanceof $this->collectionClass)) {
            return false;
        }

        return $value->getElementType()->equals($this->elementType);
    }
}