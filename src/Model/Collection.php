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
}
