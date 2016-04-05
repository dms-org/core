<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\CustomLoader\CustomLoadedEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\CustomLoader\CustomLoadedEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomLoaderTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([CustomLoadedEntity::class => CustomLoadedEntityMapper::class]);
    }

    public function testPersist()
    {
        $entity = new CustomLoadedEntity(null, 50);

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
            'entities' => [
                ['id' => 1],
            ],
        ]);
    }

    public function testLoad()
    {
        $this->setDataInDb([
            'entities' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ],
        ]);

        $this->assertEquals(new CustomLoadedEntity(1, 999), $this->repo->get(1));
        $this->assertEquals([new CustomLoadedEntity(1, 999), new CustomLoadedEntity(2, 999), new CustomLoadedEntity(3, 999)], $this->repo->getAll());
    }
}