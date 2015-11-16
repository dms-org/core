<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\Type\WithElementsType;

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
    public function isPropertyValue()
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
     * @return \Closure
     */
    public function createArrayGetterCallable()
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