<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\Criteria\MemberExpressionNode;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\ObjectType;
use Pinq\Iterators\IIteratorScheme;
use Pinq\Iterators\IOrderedMap;

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
     * @var \SplObjectStorage|null
     */
    protected $instanceMap;

    /**
     * @param string                      $objectType
     * @param \Traversable|ITypedObject[] $objects
     * @param IIteratorScheme|null        $scheme
     * @param Collection|null             $source
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        string $objectType,
        $objects = [],
        IIteratorScheme $scheme = null,
        Collection $source = null
    )
    {
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

    protected function dataToSerialize()
    {
        return $this->getObjectType();
    }


    protected function loadFromSerializedData($class)
    {
        parent::loadFromSerializedData(Type::object($class));
        $this->classDefinition = $class::definition();
    }

    protected function constructScopedSelf($elements)
    {
        return new TypedCollection(Type::mixed(), $elements, $this->scheme, $this->source ?: $this);
    }

    protected function updateElements(\Traversable $elements)
    {
        parent::updateElements($elements);

        $this->loadInstanceMap($this->toOrderedMap());
    }

    protected function toOrderedMap()
    {
        $elements = parent::toOrderedMap();

        if ($this->instanceMap === null) {
            $this->loadInstanceMap($elements);
        }

        return $elements;
    }

    protected function loadInstanceMap(IOrderedMap $elements)
    {
        $this->instanceMap = new \SplObjectStorage();

        foreach ($elements as $key => $object) {
            $this->instanceMap[$object] = $key;
        }
    }

    public function getAll() : array
    {
        return $this->toOrderedMap()->values();
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectType() : string
    {
        return $this->elementType->getClass();
    }

    public function contains($object)
    {
        $objectType = $this->getObjectType();

        if (!($object instanceof $objectType)) {
            throw Exception\TypeMismatchException::argument(__METHOD__, 'object', $objectType, $object);
        }

        return $this->doesContainsObjects([$object]);
    }

    /**
     * @inheritDoc
     */
    public function containsAll(array $objects) : bool
    {
        Exception\TypeMismatchException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->getObjectType());

        return $this->doesContainsObjects($objects);
    }

    /**
     * @param object[] $objects
     *
     * @return bool
     */
    protected function doesContainsObjects(array $objects) : bool
    {
        $this->toOrderedMap();

        foreach ($objects as $object) {
            if (!isset($this->instanceMap[$object])) {
                return false;
            }
        }

        return true;
    }

    protected function containsInstance(ITypedObject $instance)
    {
        return isset($this->instanceMap[$instance]);
    }

    /**
     * {@inheritDoc}
     */
    public function save(ITypedObject $object)
    {
        $this->saveAll([$object]);
    }

    /**
     * {@inheritDoc}
     */
    public function saveAll(array $objects)
    {
        foreach ($objects as $key => $object) {
            if ($this->containsInstance($object)) {
                unset($objects[$key]);
            }
        }

        $this->addRange($objects);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($object)
    {
        return $this->removeAll([$object]);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAll(array $objects)
    {
        Exception\InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->getObjectType());

        $lookup = [];

        foreach ($objects as $object) {
            $lookup[spl_object_hash($object)] = true;
        }

        $this->removeWhere(function (ITypedObject $other) use ($lookup) {
            return isset($lookup[spl_object_hash($other)]);
        });
    }

    public function offsetSet($index, $value)
    {
        parent::offsetSet($index, $value);
        $this->loadInstanceMap($this->toOrderedMap());
    }

    public function offsetUnset($index)
    {
        parent::offsetUnset($index);
        $this->loadInstanceMap($this->toOrderedMap());
    }

    /**
     * @inheritDoc
     */
    public function move(ITypedObject $object, int $newPosition)
    {
        $this->toOrderedMap();

        if (!$this->containsInstance($object)) {
            throw Exception\InvalidArgumentException::format(
                'Invalid call to %s: the collection does not contain the supplied object of type %s',
                __METHOD__, get_class($object)
            );
        }

        $this->moveAtIndex($this->instanceMap[$object], $newPosition);
    }

    /**
     * @inheritDoc
     */
    public function moveAtIndex($index, int $newPosition)
    {
        $currentElements = $this->toOrderedMap();

        if ($newPosition < 1 || $newPosition > $this->count()) {
            throw Exception\InvalidArgumentException::format(
                'Invalid call to %s: the position must be between 1 and %d, %d given',
                __METHOD__, $this->count(), $newPosition
            );
        }

        if (!$currentElements->contains($index)) {
            throw Exception\InvalidArgumentException::format(
                'Invalid call to %s: the collection does not contain a value with the \'%s\' index.',
                __METHOD__, $index
            );
        }

        $currentPosition = 1;
        $newOrderedMap = $this->scheme->createOrderedMap();

        foreach ($currentElements as $key => $value) {
            if ($currentPosition === $newPosition) {
                $newOrderedMap->set($index, $currentElements->get($index));
                $currentPosition++;
            }

            if ($key !== $index) {
                $newOrderedMap->set($key, $value);
                $currentPosition++;
            }
        }

        if ($currentPosition === $newPosition) {
            $newOrderedMap->set($index, $currentElements->get($index));
        }

        $this->updateElements($newOrderedMap);
    }

    /**
     * @inheritDoc
     */
    public function criteria() : Criteria\Criteria
    {
        /** @var string|TypedObject $objectType */
        $objectType = $this->getObjectType();

        return $objectType::criteria();
    }

    /**
     * @inheritDoc
     */
    public function loadCriteria() : Criteria\LoadCriteria
    {
        return new LoadCriteria($this->classDefinition);
    }

    /**
     * @inheritdoc
     */
    public function countMatching(ICriteria $criteria) : int
    {
        return count($this->matching($criteria));
    }

    /**
     * @inheritDoc
     */
    public function matching(ICriteria $criteria) : array
    {
        $criteria->verifyOfClass($this->getObjectType());

        $objects = $this->asArray();

        if ($criteria->hasCondition()) {
            $objects = call_user_func($criteria->getCondition()->getArrayFilterCallable(), $objects);
        }

        if ($criteria->hasOrderings()) {
            $multisortArgs = [];

            foreach ($criteria->getOrderings() as $ordering) {
                $direction    = $ordering->isAsc() ? \SORT_ASC : \SORT_DESC;
                $memberGetter = $ordering->getArrayOrderCallable();

                $multisortArgs[] = $memberGetter($objects);
                $multisortArgs[] = $direction;
            }

            $multisortArgs[] =& $objects;
            call_user_func_array('array_multisort', $multisortArgs);
        }

        return array_slice($objects, $criteria->getStartOffset(), $criteria->getLimitAmount(), true);
    }

    /**
     * {@inheritDoc}
     */
    public function satisfying(ISpecification $specification) : array
    {
        $specification->verifyOfClass($this->getObjectType());

        return $specification->filter($this->asArray());
    }

    /**
     * @inheritDoc
     */
    public function loadMatching(ILoadCriteria $criteria) : array
    {
        $criteria->verifyOfClass($this->getObjectType());

        $objects    = array_values($this->matching($criteria));
        $loadedData = array_fill_keys(array_keys($objects), []);

        foreach ($criteria->getAliasMemberTree() as $node) {
            $this->loadMemberNode($loadedData, $objects, $node);
        }

        return $loadedData;
    }

    protected function loadMemberNode(array &$loadedData, array $currentValues, MemberExpressionNode $node)
    {
        $getter = $node->getMemberExpression()->createArrayGetterCallable();

        $memberValues = $getter($currentValues);

        foreach ($node->getLoadAliases() as $loadAlias) {
            foreach ($memberValues as $key => $memberValue) {
                $loadedData[$key][$loadAlias] = $memberValue;
            }
        }

        foreach ($node->getChildren() as $childNode) {
            $this->loadMemberNode($loadedData, $memberValues, $childNode);
        }
    }
}
