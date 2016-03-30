<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Model\Criteria\IMemberExpression;
use Dms\Core\Model\Type\IType;

/**
 * The member expression base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class MemberExpression implements IMemberExpression
{
    /**
     * @var string
     */
    protected $expressionString;

    /**
     * @var IType
     */
    protected $sourceType;

    /**
     * @var IType
     */
    protected $resultType;

    /**
     * @param IType  $sourceType
     * @param IType  $resultType
     * @param string $expressionString
     */
    public function __construct(IType $sourceType, IType $resultType, string $expressionString)
    {
        $this->sourceType = $sourceType;

        $this->resultType = $sourceType->isNullable()
                ? $resultType->nullable()
                : $resultType;

        $this->expressionString = $expressionString;
    }

    /**
     * @inheritDoc
     */
    public function getSourceType() : IType
    {
        return $this->sourceType;
    }

    /**
     * @inheritDoc
     */
    public function getResultingType() : IType
    {
        return $this->resultType;
    }

    /**
     * @inheritDoc
     */
    public function asNullable()
    {
        $clone = clone $this;

        $clone->sourceType = $this->sourceType->nullable();
        $clone->resultType = $this->resultType->nullable();

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function asString() : string
    {
        return $this->expressionString;
    }

    /**
     * @inheritDoc
     */
    public function createGetterCallable() : callable
    {
        $arrayGetter = $this->createArrayGetterCallable();

        return function ($object) use ($arrayGetter) {
            return $arrayGetter([0 => $object])[0];
        };
    }
}