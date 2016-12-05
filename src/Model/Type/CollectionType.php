<?php declare(strict_types = 1);

namespace Dms\Core\Model\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\Type\Builder\Type;

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
    public function getCollectionClass() : string
    {
        return $this->collectionClass;
    }

    /**
     * @param IType $type
     *
     * @return bool
     */
    protected function checkThisIsSubsetOf(IType $type) : bool
    {
        if (parent::checkThisIsSubsetOf($type) ) {
            if ($type instanceof self) {
                return is_a($this->collectionClass, $type->collectionClass, true);
            }

            return true;
        }

        return false;
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
    public function isOfType($value) : bool
    {
        if (!($value instanceof $this->collectionClass)) {
            return false;
        }

        return $value->getElementType()->equals($this->elementType);
    }
}