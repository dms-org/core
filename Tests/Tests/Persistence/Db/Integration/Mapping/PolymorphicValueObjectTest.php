<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\CurrencyEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EntityWithValueObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Polymorphic\EmbeddedMoneyObjectSubClass;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\Polymorphic\EntityWithValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PolymorphicValueObjectTest extends DbIntegrationTest
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
        $entity->prefixedMoney = new EmbeddedMoneyObjectSubClass(497, CurrencyEnum::usd(), 'extra');

        return $entity;
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
                                'type'               => null,
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'extra'              => null,
                                'prefix_type'        => 'subclass',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'prefix_extra'       => 'extra',
                                'has_nullable_money' => false,
                                'nullable_type'      => null,
                                'nullable_cents'     => null,
                                'nullable_currency'  => null,
                                'nullable_extra'     => null,
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
                                'type'               => null,
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'extra'              => null,
                                'prefix_type'        => 'subclass',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'prefix_extra'       => 'extra',
                                'has_nullable_money' => true,
                                'nullable_type'      => null,
                                'nullable_cents'     => 200,
                                'nullable_currency'  => 'AUD',
                                'nullable_extra'     => null,
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
                                'type'               => null,
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'extra'              => null,
                                'prefix_type'        => 'subclass',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'prefix_extra'       => 'extra',
                                'has_nullable_money' => false,
                                'nullable_type'      => null,
                                'nullable_cents'     => null,
                                'nullable_currency'  => null,
                                'nullable_extra'     => null,
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
                                'type'               => null,
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'extra'              => null,
                                'prefix_type'        => 'subclass',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'prefix_extra'       => 'extra',
                                'has_nullable_money' => true,
                                'nullable_type'      => null,
                                'nullable_cents'     => 200,
                                'nullable_currency'  => 'AUD',
                                'nullable_extra'     => null,
                        ]
                ]
        ]);

        $entity                = $this->getTestEntity(1);
        $entity->nullableMoney = new EmbeddedMoneyObject(200, CurrencyEnum::aud());

        $this->assertEquals($entity, $this->repo->get(1));
    }

    public function testRemove()
    {
        $this->db->setData([
                'entities' => [
                        [
                                'id'                 => 1,
                                'name'               => 'some name',
                                'type'               => null,
                                'cents'              => 100,
                                'currency'           => 'AUD',
                                'extra'              => null,
                                'prefix_type'        => 'subclass',
                                'prefix_cents'       => 497,
                                'prefix_currency'    => 'USD',
                                'prefix_extra'       => 'extra',
                                'has_nullable_money' => true,
                                'nullable_type'      => null,
                                'nullable_cents'     => 200,
                                'nullable_currency'  => 'AUD',
                                'nullable_extra'     => null,
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
}