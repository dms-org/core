<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping;

use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Boolean;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\CurrencyEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EntityWithValueObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EntityWithValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return EntityWithValueObjectMapper::orm();
    }

    protected function getTestEntity($id = null)
    {
        $entity                = new EntityWithValueObject($id);
        $entity->name          = 'some name';
        $entity->money         = new EmbeddedMoneyObject(100, CurrencyEnum::aud());
        $entity->prefixedMoney = new EmbeddedMoneyObject(497, CurrencyEnum::usd());

        return $entity;
    }

    public function testNullableValueObjectHasAllNullableColumns()
    {
        $this->assertInstanceOf(Boolean::class, $this->table->getStructure()->findColumn('has_nullable_money')->getType());
        $this->assertTrue($this->table->getStructure()->findColumn('nullable_cents')->getType()->isNullable());
        $this->assertTrue($this->table->getStructure()->findColumn('nullable_currency')->getType()->isNullable());
    }

    public function testPersistWithNullValueObject()
    {
        $entity = $this->getTestEntity();

        $this->repo->save($entity);

        $this->assertTrue($entity->hasId());
        $this->assertSame(1, $entity->getId());

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'has_nullable_money' => false,
                                'nullable_cents'     => null,
                                'nullable_currency'  => null,
                        ]
                ]
        ]);
    }

    public function testPersistWithSetValueObject()
    {
        $entity                = $this->getTestEntity();
        $entity->nullableMoney = new EmbeddedMoneyObject(200, CurrencyEnum::aud());

        $this->repo->save($entity);

        $this->assertTrue($entity->hasId());
        $this->assertSame(1, $entity->getId());

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'has_nullable_money' => true,
                                'nullable_cents'     => 200,
                                'nullable_currency'  => 'AUD',
                        ]
                ]
        ]);
    }

    public function testLoadNullValueObject()
    {
        $this->db->setData([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'has_nullable_money' => false,
                                'nullable_cents'     => null,
                                'nullable_currency'  => null,
                        ]
                ]
        ]);

        $entity = $this->getTestEntity(1);

        $this->assertEquals($entity, $this->repo->get(1));
    }

    public function testLoadFullValueObject()
    {
        $this->db->setData([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'has_nullable_money' => true,
                                'nullable_cents'     => 200,
                                'nullable_currency'  => 'AUD',
                        ]
                ]
        ]);

        $entity                = $this->getTestEntity(1);
        $entity->nullableMoney = new EmbeddedMoneyObject(200, CurrencyEnum::aud());

        $this->assertEquals($entity, $this->repo->get(1));
    }

    public function testOutOfSyncNullableColumns()
    {
        $this->db->setData([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'has_nullable_money' => false,
                                'nullable_cents'     => 200,
                                'nullable_currency'  => 'USD',
                        ]
                ]
        ]);

        /** @var EntityWithValueObject $entity */
        $entity = $this->repo->get(1);
        $this->assertNull($entity->nullableMoney);
        $this->repo->save($entity);


        $this->assertDatabaseDataSameAs([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'has_nullable_money' => false,
                                'nullable_cents'     => null,
                                'nullable_currency'  => null,
                        ]
                ]
        ]);
    }

    public function testRemove()
    {
        $this->db->setData([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'has_nullable_money' => true,
                                'nullable_cents'     => 200,
                                'nullable_currency'  => 'AUD',
                        ]
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'entities' => []
        ]);

        $this->assertExecutedQueryTypes([
                'Delete entity' => Delete::class
        ]);
    }

    public function testLoadPartial()
    {
        $this->db->setData([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'cents'              => 101,
                                'currency'           => 'AUD',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'has_nullable_money' => false,
                                'nullable_cents'     => null,
                                'nullable_currency'  => null,
                        ]
                ]
        ]);

        $this->assertEquals(
                [
                        [
                                'name'                   => 'some name',
                                'money.cents'            => 101,
                                'nullableMoney.currency' => null,
                                'prefixedMoney'          => new EmbeddedMoneyObject(497, CurrencyEnum::usd()),
                        ]
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll(['name', 'money.cents', 'nullableMoney.currency', 'prefixedMoney'])
                )
        );
    }
}