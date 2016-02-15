<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Type\IType;

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
    public function __construct(IType $sourceType, string $methodName, NestedMember $member, IEntitySet $dataSource, IType $returnType)
    {
        parent::__construct($sourceType, $methodName, [$member->asString()], $returnType);
        $this->dataSource = $dataSource;
        $this->member     = $member;
    }

    /**
     * @return NestedMember
     */
    public function getIdMember() : \Dms\Core\Model\Criteria\NestedMember
    {
        return $this->member;
    }

    /**
     * @return IEntitySet
     */
    public function getDataSource() : \Dms\Core\Model\IEntitySet
    {
        return $this->dataSource;
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
}