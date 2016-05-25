<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\MemberMapping;

/**
 * Contains a member mapping instance and its associated table alias
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberMappingWithTableAliases
{
    /**
     * @var MemberMapping
     */
    protected $mapping;

    /**
     * @var string[]
     */
    protected $tableAliases = [];

    /**
     * MemberMappingWithTableAlias constructor.
     *
     * @param MemberMapping $mapping
     * @param string[]      $tableAliases
     */
    public function __construct(MemberMapping $mapping, array $tableAliases)
    {
        $this->mapping      = $mapping;
        $this->tableAliases = $tableAliases;
    }

    /**
     * @return MemberMapping
     */
    public function getMapping() : MemberMapping
    {
        return $this->mapping;
    }

    /**
     * @return string[]
     */
    public function getTableAliases() : array 
    {
        return $this->tableAliases;
    }

    /**
     * @return string
     */
    public function getLastTableAlias() : string
    {
        return end($this->tableAliases);
    }

    /**
     * @return string
     * @throws InvalidOperationException
     */
    public function getSecondLastTableAlias() : string
    {
        if (count($this->tableAliases) < 2) {
            throw InvalidOperationException::format('Invalid call to %s: not enough tables', __METHOD__);
        }

        return array_slice($this->tableAliases, -2)[0];
    }
}