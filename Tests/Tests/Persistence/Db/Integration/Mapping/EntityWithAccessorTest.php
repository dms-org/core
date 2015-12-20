<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Accessors\EntityWithAccessor;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Accessors\EntityWithAccessorMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithAccessorTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([EntityWithAccessor::class => EntityWithAccessorMapper::class]);
    }

    public function testPersist()
    {
        $entity = new EntityWithAccessor(null, 'abc');

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
            'entities' => [
                ['id' => 1, 'value' => 'abc']
            ]
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1, 'value' => 'abc']
                ]
        ]);

        $this->assertEquals(new EntityWithAccessor(1, 'abc'), $this->repo->get(1));
    }
}