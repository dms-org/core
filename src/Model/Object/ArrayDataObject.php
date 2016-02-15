<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Util\Debug;

/**
 * The array data transfer object class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayDataObject extends ValueObject implements IDataTransferObject, \ArrayAccess, \JsonSerializable
{
    /**
     * @var array
     */
    protected $data;

    /**
     * ArrayDataObject constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->data)->asArrayOf(Type::mixed());
    }

    /**
     * Gets the underlying array.
     *
     * @return array
     */
    public function getArray() : array
    {
        return $this->data;
    }

    /**
     * Returns whether the index exists.
     *
     * @param int|string $index
     *
     * @return bool
     */
    public function offsetExists($index) : bool
    {
        return isset($this->data[$index]);
    }

    /**
     * Gets the value at the supplied index.
     *
     * @param int|string $index
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function offsetGet($index)
    {
        if (!array_key_exists($index, $this->data)) {
            throw InvalidArgumentException::format(
                    'Invalid array object index: expecting one of (%s), %s given',
                    Debug::formatValues(array_keys($this->data)), is_string($index) ? "'{$index}'" : $index
            );
        }

        return $this->data[$index];
    }

    /**
     * Returns a new array data object with the supplied array data
     * added to the existing data.
     *
     * @param array $data
     *
     * @return static
     */
    public function with(array $data)
    {
        return new ArrayDataObject($data + $this->data);
    }

    /**
     * Returns a new array data object with the existing data
     * without the supplied keys.
     *
     * @param array $keys
     *
     * @return static
     */
    public function without(array $keys)
    {
        $keys = array_fill_keys($keys, true);

        return new ArrayDataObject(array_diff_key($this->data, $keys));
    }

    /**
     * Not implemented.
     *
     * Use the following method instead.
     *
     * @see with
     *
     * @param mixed $index
     * @param mixed $value
     *
     * @throws NotImplementedException
     */
    public function offsetSet($index, $value)
    {
        throw NotImplementedException::format(
                'Invalid call to %s: cannot set array index \'%s\' on class %s as it is immutable, use %s::%s method instead',
                __METHOD__, $index, __CLASS__, __CLASS__, 'with'
        );
    }

    /**
     * Not implemented.
     *
     * Use the following method instead.
     *
     * @see without
     *
     * @param mixed $index
     *
     * @throws NotImplementedException
     */
    public function offsetUnset($index)
    {
        throw NotImplementedException::format(
                'Invalid call to %s: cannot unset array index \'%s\' on class %s as it is immutable, use %s::%s method instead',
                __METHOD__, $index, __CLASS__, __CLASS__, 'without'
        );
    }

    /**
     * Gets the data which can be serialized as json
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->data;
    }
}