<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping;

use Iddigital\Cms\Core\Model\Object\Type\DateTime;
use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\DateTimeValueObject\EntityWithDateTime;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\DateTimeValueObject\EntityWithDateTimeMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeValueObjectTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([EntityWithDateTime::class => EntityWithDateTimeMapper::class]);
    }

    public function testPersist()
    {
        $entity = new EntityWithDateTime(null, DateTime::fromString('2000-01-01 10:11:12'));

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        ['id' => 1, 'datetime' => '2000-01-01 10:11:12']
                ]
        ]);
    }

    public function testLoad()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1, 'datetime' => '2000-01-01 10:11:12']
                ]
        ]);

        $entity = new EntityWithDateTime(1, DateTime::fromString('2000-01-01 10:11:12'));

        $this->assertEquals($entity, $this->repo->get(1));
    }

    public function testRemove()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1, 'datetime' => '2000-01-01 10:11:12']
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                ]
        ]);
    }

    public function testLoadPartial()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1, 'datetime' => '2000-01-01 10:11:12']
                ]
        ]);

        $this->assertEquals(
                [
                        [
                                'datetime' => DateTime::fromString('2000-01-01 10:11:12'),
                        ]
                ],
                $this->repo->loadPartial(
                        $this->repo->loadCriteria()
                                ->load('datetime')
                )
        );
    }
}