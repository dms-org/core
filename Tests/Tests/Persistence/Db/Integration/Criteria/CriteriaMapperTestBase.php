<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\ICriteria;
use Iddigital\Cms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class CriteriaMapperTestBase extends CmsTestCase
{
    /**
     * @var CriteriaMapper
     */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = $this->buildMapper();
    }

    abstract protected function buildMapper();

    protected function assertMappedSelect(ICriteria $criteria, Select $select, CriteriaMapper $mapper = null)
    {
        $mapper = $mapper ?: $this->mapper;

        $this->assertEquals($select, $mapper->mapCriteriaToSelect($criteria));
    }

    /**
     * @return Select
     */
    protected function select()
    {
        /** @var IEntityMapper $mapper */
        $mapper = $this->mapper->getMapper();
        return Select::from($mapper->getPrimaryTable());
    }

    /**
     * @return Select
     */
    protected function selectAllColumns()
    {
        /** @var IEntityMapper $mapper */
        $mapper = $this->mapper->getMapper();
        return Select::allFrom($mapper->getPrimaryTable());
    }

    protected function column($name)
    {
        /** @var IEntityMapper $mapper */
        $mapper = $this->mapper->getMapper();
        return Expr::tableColumn($mapper->getPrimaryTable(), $name);
    }

    protected function columnType($name)
    {
        return $this->column($name)->getColumn()->getType();
    }
}