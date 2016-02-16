<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\CollectionType;
use Pinq\Collection as PinqCollection;
use Pinq\Iterators\IIteratorScheme;

/**
 * The entity id collection class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class EntityIdCollection extends TypedCollection
{
    public function __construct(
            $values = [],
            IIteratorScheme $scheme = null,
            PinqCollection $source = null
    ) {
        parent::__construct(Type::int(), $values, $scheme, $source);
    }

    protected function constructScopedSelf($elements)
    {
        return new TypedCollection(Type::mixed(), $elements, $this->scheme, $this->source ?: $this);
    }

    /**
     * Returns the type of the collection
     *
     * @return CollectionType
     */
    final public static function type() : CollectionType
    {
        return new CollectionType(Type::int(), get_called_class());
    }
}
