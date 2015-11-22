<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The load id method expression base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class LoadIdFromEntitySetMethodExpression extends MethodExpression
{
    /**
     * @var NestedMember
     */
    protected $member;

    /**
     * @var IEntitySet
     */
    protected $dataSource;

    /**
     * @param IType        $sourceType
     * @param string       $methodName
     * @param NestedMember $member
     * @param IEntitySet   $dataSource
     * @param IType        $returnType
     */
    public function __construct(IType $sourceType, $methodName, NestedMember $member, IEntitySet $dataSource, IType $returnType)
    {
        parent::__construct($sourceType, $methodName, [$member->asString()], $returnType);
        $this->dataSource = $dataSource;
        $this->member     = $member;
    }

    /**
     * @return NestedMember
     */
    public function getIdMember()
    {
        return $this->member;
    }

    /**
     * @return IEntitySet
     */
    public function getDataSource()
    {
        return $this->dataSource;
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
}