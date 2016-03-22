<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\EntityWithEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\EntityWithEnumAsVarcharsMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\GenderEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Enum\StatusEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types\TypesEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumsAsVarcharsTest extends EnumsTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return CustomOrm::from([EntityWithEnum::class => EntityWithEnumAsVarcharsMapper::class]);
    }

    public function testCorrectTableStructure()
    {
        $this->assertEquals([
            'id'              => PrimaryKeyBuilder::incrementingInt('id'),
            'status'          => new Column('status', new Varchar(255)),
            'nullable_status' => new Column('nullable_status', (new Varchar(255))->nullable()),
            'gender'          => new Column('gender', new Varchar(1)),
            'nullable_gender' => new Column('nullable_gender', (new Varchar(1))->nullable()),
        ], $this->table->getStructure()->getColumns());
    }

    /**
     * @return EntityWithEnum
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
                    'gender'          => GenderEnum::MALE,
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
                    'gender'          => GenderEnum::MALE,
                    'nullable_gender' => GenderEnum::FEMALE,
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

    public function testLoadPartial()
    {
        $this->setDataInDb([
            'data' => [
                [
                    'id'              => 1,
                    'status'          => StatusEnum::ACTIVE,
                    'nullable_status' => null,
                    'gender'          => GenderEnum::MALE,
                    'nullable_gender' => GenderEnum::FEMALE,
                ]
            ]
        ]);

        $this->assertEquals(
            [
                [
                    'status'               => StatusEnum::active(),
                    'status.value'         => StatusEnum::ACTIVE,
                    'nullableStatus'       => null,
                    'nullableStatus.value' => null,
                    'gender'               => GenderEnum::male(),
                ]
            ],
            $this->repo->loadMatching(
                $this->repo->loadCriteria()
                    ->loadAll(['status', 'status.value', 'nullableStatus', 'nullableStatus.value', 'gender'])
            )
        );
    }
}