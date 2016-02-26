<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Unique\EmbeddedUniqueValueObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Unique\EntityWithUniqueValueObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Unique\EntityWithUniqueValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UniqueValueObjectTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return EntityWithUniqueValueObjectMapper::orm();
    }

    public function testCreatesUniqueIndex()
    {
        $this->assertEquals(
                [
                        new Index(
                                'entities_unique_int_unique_index',
                                true,
                                ['unique_int']
                        )
                ],
                array_values($this->getSchemaTable('entities')->getIndexes())
        );
    }

    public function testLoad()
    {
        $this->setDataInDb([
                'entities' => [
                        ['id' => 1, 'unique_int' => 1],
                        ['id' => 2, 'unique_int' => 4],
                        ['id' => 3, 'unique_int' => 5],
                ]
        ]);

        $this->assertEquals([
                new EntityWithUniqueValueObject(1, new EmbeddedUniqueValueObject(1)),
                new EntityWithUniqueValueObject(2, new EmbeddedUniqueValueObject(4)),
                new EntityWithUniqueValueObject(3, new EmbeddedUniqueValueObject(5)),
        ], $this->repo->getAll());
    }

    public function testPersist()
    {
        $this->repo->saveAll([
                new EntityWithUniqueValueObject(null, new EmbeddedUniqueValueObject(1)),
                new EntityWithUniqueValueObject(null, new EmbeddedUniqueValueObject(4)),
                new EntityWithUniqueValueObject(null, new EmbeddedUniqueValueObject(5)),
        ]);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        ['id' => 1, 'unique_int' => 1],
                        ['id' => 2, 'unique_int' => 4],
                        ['id' => 3, 'unique_int' => 5],
                ]
        ]);
    }
}