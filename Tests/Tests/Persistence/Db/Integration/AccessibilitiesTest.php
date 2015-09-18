<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Accessbilities\AccessibilitiesEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Accessbilities\AccessibilitiesEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Types\TypesEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AccessibilitiesTest extends DbIntegrationTest
{
    /**
     * @return IEntityMapper
     */
    protected function loadMapper()
    {
        return new AccessibilitiesEntityMapper('accessibilities');
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