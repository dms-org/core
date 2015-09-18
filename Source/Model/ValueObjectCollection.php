<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Pinq\Collection;
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
            $valueObjectType,
            $valueObjects = [],
            IIteratorScheme $scheme = null,
            Collection $source = null
    ) {
        if (!is_a($valueObjectType, IValueObject::class, true)) {
            throw Exception\InvalidArgumentException::format(
                    'Invalid value object class: expecting instance of %s, %s given',
                    IValueObject::class, $valueObjectType
            );
        }

        parent::__construct($valueObjectType, $valueObjects, $scheme, $source);
    }
}
