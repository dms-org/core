<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Accessbilities\AccessibilitiesEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Accessbilities\AccessibilitiesEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AccessibilitiesTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([AccessibilitiesEntity::class => AccessibilitiesEntityMapper::class]);
    }

    protected function getTestEntity()
    {
        $entity = new AccessibilitiesEntity();
        $entity->setPrivate(100);
        $entity->setProtected(200);
        $entity->public = 300;

        return $entity;
    }

    public function testPersist()
    {
        $entity = $this->getTestEntity();

        $this->repo->save($entity);

        $this->assertSame(
                [
                        1 => [
                                'id'        => 1,
                                'private'   => 100,
                                'protected' => 200,
                                'public'    => 300,
                        ]
                ],
                $this->table->getRows()
        );
    }

    public function testLoad()
    {
        $entity = $this->getTestEntity();

        $this->repo->save($entity);

        $this->assertTrue($this->repo->has(1));
        $this->assertEquals($entity, $this->repo->get(1));
    }
}