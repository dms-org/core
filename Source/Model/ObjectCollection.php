<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\PartialLoadCriteria;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\ObjectType;
use Pinq\Collection;
use Pinq\Direction;
use Pinq\Iterators\IIteratorScheme;

/**
 * The object collection class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ObjectCollection extends TypedCollection implements ITypedObjectCollection
{
    /**
     * @var ObjectType
     */
    protected $elementType;

    /**
     * @var FinalizedClassDefinition
     */
    protected $classDefinition;

    /**
     * @param string               $objectType
     * @param ITypedObject[]       $objects
     * @param IIteratorScheme|null $scheme
     * @param Collection|null      $source
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
            $objectType,
            $objects = [],
            IIteratorScheme $scheme = null,
            Collection $source = null
    ) {
        if (!is_a($objectType, ITypedObject::class, true)) {
            throw Exception\InvalidArgumentException::format(
                    'Invalid object class: expecting instance of %s, %s given',
                    ITypedObject::class, $objectType
            );
        }

        parent::__construct(Type::object($objectType), $objects, $scheme, $source);

        if (is_a($objectType, TypedObject::class, true)) {
            /** @var string|TypedObject $objectType */
            $objectType            = $this->getObjectType();
            $this->classDefinition = $objectType::definition();
        }
    }

    protected function constructScopedSelf($elements)
    {
        return new TypedCollection(Type::mixed(), $elements, $this->scheme, $this->source ?: $this);
    }

    public function getAll()
    {
        return $this->toOrderedMap()->values();
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectType()
    {
        return $this->elementType->getClass();
    }

    /**
     * @inheritDoc
     */
    public function criteria()
    {
        /** @var string|TypedObject $objectType */
        $objectType = $this->getObjectType();

        return $objectType::criteria();
    }

    /**
     * @inheritDoc
     */
    public function partialCriteria()
    {
        return new PartialLoadCriteria($this->classDefinition);
    }


    public function countMatching(ICriteria $criteria)
    {
        return count($this->matching($criteria));
    }

    /**
     * @inheritDoc
     */
    public function matching(ICriteria $criteria)
    {
        $criteria->verifyOfClass($this->getObjectType());

        $collection = $this;

        if ($criteria->hasCondition()) {
            $collection = $collection->where($criteria->getCondition()->getFilterCallable());
        }

        $first = true;
        foreach ($criteria->getOrderings() as $ordering) {
            $direction = $ordering->isAsc() ? Direction::ASCENDING : Direction::DESCENDING;

            if ($first) {
                $collection = $collection->orderBy($ordering->getOrderCallable(), $direction);
                $first      = false;
            } else {
                $collection = $collection->thenBy($ordering->getOrderCallable(), $direction);
            }
        }

        $collection = $collection->slice($criteria->getStartOffset(), $criteria->getLimitAmount());

        return $collection->asArray();
    }

    /**
     * {@inheritDoc}
     */
    public function satisfying(ISpecification $specification)
    {
        $specification->verifyOfClass($this->getObjectType());

        return $this->where($specification->getCondition()->getFilterCallable())->asArray();
    }

    /**
     * @inheritDoc
     */
    public function loadPartial(IPartialLoadCriteria $criteria)
    {
        $criteria->verifyOfClass($this->getObjectType());

        $objects    = array_values($this->matching($criteria));
        $loadedData = array_fill_keys(array_keys($objects), []);

        foreach ($criteria->getAliasNestedPropertyMap() as $index => $property) {
            $getter = $property->makePropertyGetterCallable();

            foreach ($objects as $key => $object) {
                $loadedData[$key][$index] = $getter($object);
            }
        }

        return $loadedData;
    }

}
