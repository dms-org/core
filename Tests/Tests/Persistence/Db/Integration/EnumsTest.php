<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Enum\EntityWithEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Enum\EntityWithEnumMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Enum\GenderEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Enum\StatusEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\Types\TypesEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumsTest extends DbIntegrationTest
{
    /**
     * @return IEntityMapper
     */
    protected function loadMapper()
    {
        return new EntityWithEnumMapper();
    }

    /**
     * @return TypesEntity
     */
    protected function getTestEntityWithNulls()
    {
        $entity = new EntityWithEnum();

        $entity->status         = StatusEnum::active();
        $entity->nullableStatus = null;
        $entity->gender         = GenderEnum::male();
        $entity->nullableGender = null;

        return $entity;
    }

    protected function getTestEntityWithoutNulls()
    {
        $entity = new EntityWithEnum();

        $entity->status         = StatusEnum::active();
        $entity->nullableStatus = StatusEnum::inactive();
        $entity->gender         = GenderEnum::male();
        $entity->nullableGender = GenderEnum::female();

        return $entity;
    }

    public function testPersistWithNulls()
    {
        $entity = $this->getTestEntityWithNulls();

        $this->repo->save($entity);

        $this->assertSame(
                [
                        1 => [
                                'id'              => 1,
                                'status'          => StatusEnum::ACTIVE,
                                'nullable_status' => null,
                                'gender'          => 'M',
                                'nullable_gender' => null,
                        ]
                ],
                $this->table->getRows()
        );
    }

    public function testPersistWithoutNulls()
    {
        $entity = $this->getTestEntityWithoutNulls();

        $this->repo->save($entity);

        $this->assertSame(
                [
                        1 => [
                                'id'              => 1,
                                'status'          => StatusEnum::ACTIVE,
                                'nullable_status' => StatusEnum::INACTIVE,
                                'gender'          => 'M',
                                'nullable_gender' => 'F',
                        ]
                ],
                $this->table->getRows()
        );
    }

    public function testLoadWithNulls()
    {
        $entity = $this->getTestEntityWithNulls();

        $this->repo->save($entity);

        $this->assertEquals($entity, $this->repo->get(1));
    }

    public function testLoadWithoutNulls()
    {
        $entity = $this->getTestEntityWithoutNulls();

        $this->repo->save($entity);

        $this->assertEquals($entity, $this->repo->get(1));
    }
}