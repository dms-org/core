<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\EntityWithEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\EntityWithEnumMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\GenderEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\StatusEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types\TypesEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumsTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return CustomOrm::from([EntityWithEnum::class => EntityWithEnumMapper::class]);
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