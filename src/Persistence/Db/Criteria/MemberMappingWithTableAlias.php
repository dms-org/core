<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Persistence\Db\Criteria\MemberMapping\MemberMapping;

/**
 * Contains a member mapping instance and its associated table alias
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberMappingWithTableAlias
{
    /**
     * @var MemberMapping
     */
    protected $mapping;

    /**
     * @var string
     */
    protected $tableAlias;

    /**
     * MemberMappingWithTableAlias constructor.
     *
     * @param MemberMapping $mapping
     * @param string        $tableAlias
     */
    public function __construct(MemberMapping $mapping, string $tableAlias)
    {
        $this->mapping    = $mapping;
        $this->tableAlias = $tableAlias;
    }

    /**
     * @return MemberMapping
     */
    public function getMapping() : MemberMapping
    {
        return $this->mapping;
    }

    /**
     * @return string
     */
    public function getTableAlias() : string
    {
        return $this->tableAlias;
    }
}