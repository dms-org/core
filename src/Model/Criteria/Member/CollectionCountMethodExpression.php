<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;
use Dms\Core\Model\Type\WithElementsType;

/**
 * The object set count method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CollectionCountMethodExpression extends MethodExpression
{
    const METHOD_NAME = 'count';

    /**
     * @inheritDoc
     */
    public function __construct(IType $sourceType)
    {
        parent::__construct($sourceType, self::METHOD_NAME, [], Type::int());

        if (!($sourceType->nonNullable() instanceof WithElementsType)) {
            throw InvalidArgumentException::format(
                    'Invalid source type passed to %s: expecting collection type, %s given',
                    __METHOD__, $sourceType->nonNullable()->asTypeString()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function isPropertyValue() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getProperty()
    {
        return null;
    }

    /**
     * @return callable
     */
    public function createArrayGetterCallable() : callable
    {
        return function (array $collections) {
            $results = [];

            foreach ($collections as $key => $collection) {
                /** @var array|\Countable|null $collection */
                $results[$key] = $collection === null
                        ? null
                        : count($collection);
            }

            return $results;
        };
    }
}