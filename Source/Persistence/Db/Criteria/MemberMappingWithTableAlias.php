<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria;

use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping\MemberMapping;

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
    public function __construct(MemberMapping $mapping, $tableAlias)
    {
        $this->mapping    = $mapping;
        $this->tableAlias = $tableAlias;
    }

    /**
     * @return MemberMapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }
}