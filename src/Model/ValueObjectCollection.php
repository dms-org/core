<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Pinq\Iterators\IIteratorScheme;

/**
 * The value object collection class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ValueObjectCollection extends ObjectCollection implements IValueObjectCollection
{
    /**
     * @param string               $valueObjectType
     * @param IValueObject[]       $valueObjects
     * @param IIteratorScheme|null $scheme
     * @param Collection|null      $source
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        string $valueObjectType,
        array $valueObjects = [],
        IIteratorScheme $scheme = null,
        Collection $source = null
    )
    {
        if (!is_a($valueObjectType, IValueObject::class, true)) {
            throw Exception\InvalidArgumentException::format(
                'Invalid value object class: expecting instance of %s, %s given',
                IValueObject::class, $valueObjectType
            );
        }

        parent::__construct($valueObjectType, array_values($valueObjects), $scheme, $source);
    }

    /**
     * Performs a value-wise comparison to see if objects
     * are equal.
     *
     * @param IValueObject[] $objects
     *
     * @return bool
     */
    protected function doesContainsObjects(array $objects) : bool
    {
        $objectsLookup = $this->asArray();

        foreach ($objects as $object) {
            if (!in_array($object, $objectsLookup, false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getObjectId(ITypedObject $object)
    {
        Exception\TypeMismatchException::verifyInstanceOf(__METHOD__, 'object', $object, $this->getObjectType());

        if (isset($this->instanceMap[$object])) {
            return $this->instanceMap[$object];
        }

        throw Exception\InvalidArgumentException::format(
            'Invalid call to %s: the supplied value object of type %s is not within the collection',
            __METHOD__, get_class($object)
        );
    }

    /**
     * @inheritDoc
     */
    public function removeById($id)
    {
        $this->removeAllById([$id]);
    }

    /**
     * @inheritDoc
     */
    public function removeAllById(array $ids)
    {
        $ids = array_flip($ids);

        $this->removeWhere(function ($object, $index) use ($ids) {
            return isset($ids[$index]);
        });
    }

    /**
     * @inheritDoc
     */
    public function has($index) : bool
    {
        return $this->offsetExists($index);
    }

    /**
     * @inheritDoc
     */
    public function hasAll(array $indexes) : bool
    {
        foreach ($indexes as $index) {
            if (!$this->offsetExists($index)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function get($index)
    {
        Exception\InvalidArgumentException::verify(ctype_digit((string)$index), 'The index must be a valid integer');

        $index = (int)$index;
        if (!$this->offsetExists($index)) {
            throw new ObjectNotFoundException($this->getObjectType(), $index);
        }

        return $this->offsetGet($index);
    }

    /**
     * @inheritDoc
     */
    public function getAllById(array $indexes) : array
    {
        $objects = [];

        foreach ($indexes as $index) {
            $objects[] = $this->get($index);
        }

        return $objects;
    }

    /**
     * @inheritDoc
     */
    public function tryGet($index)
    {
        return $this->offsetGet($index);
    }

    /**
     * @inheritDoc
     */
    public function tryGetAll(array $indexes) : array
    {
        $objects = [];

        foreach ($indexes as $index) {
            if ($this->offsetExists($index)) {
                $objects[] = $this->offsetGet($index);
            }
        }

        return $objects;
    }

    /**
     * @inheritDoc
     */
    public function update(IValueObject $object, IValueObject $newObject)
    {
        Exception\TypeMismatchException::verifyInstanceOf(__METHOD__, 'object', $object, $this->getObjectType());
        Exception\TypeMismatchException::verifyInstanceOf(__METHOD__, 'newObject', $newObject, $this->getObjectType());

        foreach ($this->getTrueIterator() as $key => $other) {
            if ($other === $object) {
                $this->offsetSet($key, $newObject);
                return;
            }
        }

        throw Exception\InvalidArgumentException::format(
            'Invalid call to %s: the supplied object of type %s cannot be found in the collection',
            __METHOD__, $this->getObjectType()
        );
    }

    /**
     * @inheritDoc
     */
    public function updateAtIndex(int $index, IValueObject $newObject)
    {
        Exception\InvalidArgumentException::verify($this->offsetExists($index), 'The index \'%d\' does not exist in the collection of type %s', $index, __CLASS__);
        Exception\TypeMismatchException::verifyInstanceOf(__METHOD__, 'newObject', $newObject, $this->getObjectType());

        $this->offsetSet($index, $newObject);
    }
}
