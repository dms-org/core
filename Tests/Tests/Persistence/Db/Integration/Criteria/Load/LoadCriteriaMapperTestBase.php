<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Criteria\Load;

use Iddigital\Cms\Core\Model\ILoadCriteria;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MappedLoadQuery;
use Iddigital\Cms\Core\Persistence\Db\Criteria\LoadCriteriaMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Criteria\CriteriaMapperTestBase;

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