<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria\Load;

use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Persistence\Db\Criteria\MappedLoadQuery;
use Dms\Core\Persistence\Db\Criteria\LoadCriteriaMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Criteria\CriteriaMapperTestBase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class LoadCriteriaMapperTestBase extends CriteriaMapperTestBase
{
    /**
     * @var LoadCriteriaMapper
     */
    protected $loadMapper;

    protected function setUp()
    {
        parent::setUp();
        $this->loadMapper = new LoadCriteriaMapper($this->mapper);
    }

    protected function assertMappedLoadQuery(ILoadCriteria $criteria, MappedLoadQuery $expected)
    {
        $this->assertEquals($expected, $this->loadMapper->mapLoadCriteriaToQuery($criteria));
    }
}