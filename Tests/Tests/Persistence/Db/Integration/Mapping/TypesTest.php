<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types\TypesEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Types\TypesMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypesTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return CustomOrm::from([TypesEntity::class => TypesMapper::class]);
    }

    public function testLoadEmpty()
    {
        $this->assertSame([], $this->repo->getAll());
    }

    /**
     * @return TypesEntity
     */
    protected function getTestEntity()
    {
        $entity           = new TypesEntity();
        $entity->null     = null;
        $entity->int      = 12;
        $entity->string   = 'abc';
        $entity->bool     = true;
        $entity->float    = 123.4;
        $entity->date     = new \DateTimeImmutable('2000-01-01 00:00:00');
        $entity->time     = new \DateTimeImmutable('1970-01-01 12:30:50');
        $entity->datetime = new \DateTimeImmutable('2010-03-04 12:34:56');

        return $entity;
    }

    public function testPersist()
    {
        $entity = $this->getTestEntity();

        $this->repo->save($entity);

        $this->assertTrue($entity->hasId());
        $this->assertSame(1, $entity->getId());

        $this->assertSame(
                [
                        1 => [
                                'id'       => 1,
                                'null'     => null,
                                'int'      => 12,
                                'string'   => 'abc',
                                'bool'     => true,
                                'float'    => 123.4,
                                'date'     => '2000-01-01',
                                'time'     => '12:30:50',
                                'datetime' => '2010-03-04 12:34:56',
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