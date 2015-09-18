<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration;

use Iddigital\Cms\Core\Model\Object\Type\DateTime;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Boolean;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\DateTimeValueObject\EntityWithDateTime;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\DateTimeValueObject\EntityWithDateTimeMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\CurrencyEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\EmbeddedMoneyObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\EntityWithValueObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\EntityWithValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeValueObjectTest extends DbIntegrationTest
{
    /**
     * @return IEntityMapper
     */
    protected function loadMapper()
    {
        return new EntityWithDateTimeMapper();
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
}