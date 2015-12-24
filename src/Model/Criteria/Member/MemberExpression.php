<?php

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
    public function __construct(IType $sourceType, IType $resultType, $expressionString)
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
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @inheritDoc
     */
    public function getResultingType()
    {
        return $this->resultType;
    }

    /**
     * @inheritDoc
     */
    public function asString()
    {
        return $this->expressionString;
    }

    /**
     * @inheritDoc
     */
    public function createGetterCallable()
    {
        $arrayGetter = $this->createArrayGetterCallable();

        return function ($object) use ($arrayGetter) {
            return $arrayGetter([0 => $object])[0];
        };
    }
}