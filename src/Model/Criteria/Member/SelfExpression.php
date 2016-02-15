<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Model\Type\IType;

/**
 * The self expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SelfExpression extends MemberExpression
{
    const IDENTIFIER = 'this';

    /**
     * @inheritDoc
     */
    public function __construct(IType $selfType)
    {
        parent::__construct($selfType, $selfType, self::IDENTIFIER);
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
     * @inheritDoc
     */
    public function createArrayGetterCallable() : callable
    {
        return function (array $values) {
            return $values;
        };
    }
}