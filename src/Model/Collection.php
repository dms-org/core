<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Pinq\Iterators\SchemeProvider;

/**
 * The collection class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class Collection extends \Pinq\Collection implements ICollection, \Serializable
{
    /**
     * @return string
     */
    final public function serialize() : string
    {
        return serialize([$this->asArray(), $this->dataToSerialize()]);
    }

    protected function dataToSerialize()
    {
        return null;
    }

    /**
     * @param string $serialized
     */
    final public function unserialize($serialized)
    {
        $this->scheme = SchemeProvider::getDefault();

        list($elements, $data) = unserialize($serialized);

        $this->elements = $this->scheme->createOrderedMap(
            $this->scheme->arrayIterator($elements)
        );

        $this->loadFromSerializedData($data);
    }

    protected function loadFromSerializedData($data)
    {

    }

    /**
     * @inheritdoc
     */
    public function asArray()
    {
        $array = [];

        foreach (parent::asArray() as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Inserts the element at the supplied (0-based) index.
     *
     * This will reindex the collection.
     *
     * @param int   $index
     * @param mixed $value
     *
     * @return void
     */
    public function insertAt(int $index, $value)
    {
        $array = $this->reindex()->asArray();

        Exception\InvalidArgumentException::verify(
            $index <= count($array),
            'The supplied index must not be greater than the amount of elements: expecting <= %s, %s given',
            count($array), $index
        );

        array_splice($array, $index, 0, [$value]);
        $this->updateElements(
            $this->scheme->createOrderedMap(
                $this->scheme->arrayIterator($array)
            )
        );
    }
}
