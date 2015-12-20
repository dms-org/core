<?php

namespace Dms\Core\Tests\Persistence\Db;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\IdentityMap;
use Dms\Core\Tests\Persistence\Db\Fixtures\MockEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IdentityMapTest extends CmsTestCase
{
    public function testGetters()
    {
        $map = new IdentityMap(MockEntity::class);

        $this->assertSame([], $map->getEntities());
        $this->assertSame(MockEntity::class, $map->getEntityType());
        $this->assertSame(null, $map->get(1));
    }

    public function testThrowsOnWrongEntityType()
    {
        $this->setExpectedException(TypeMismatchException::class);
        $map = new IdentityMap(MockEntity::class);
        $map->add($this->getMockForAbstractClass(IEntity::class));
    }

    public function testWorksWithEntityWithoutId()
    {
        $map = new IdentityMap(MockEntity::class);
        $map->add($entity = new MockEntity());
        $entity->setId(5);
        $this->assertTrue($map->has(5));
        $this->assertSame($entity, $map->get(5));
        $this->assertTrue($map->remove(5));
    }

    public function testAdd()
    {
        $map = new IdentityMap(MockEntity::class);

        $this->assertTrue($map->add($first = new MockEntity(1)));
        $this->assertTrue($map->add($second = new MockEntity(2)));
        $this->assertFalse($map->add(new MockEntity(1)));
        $this->assertSame($first, $map->get(1));
        $this->assertSame($second, $map->get(2));
    }

    public function testHas()
    {
        $map = new IdentityMap(MockEntity::class);

        $this->assertFalse($map->has(1));
        $this->assertTrue($map->add(new MockEntity(1)));
        $this->assertTrue($map->add(new MockEntity(2)));
        $this->assertTrue($map->has(1));
        $this->assertTrue($map->has(2));
    }

    public function testRemove()
    {
        $map = new IdentityMap(MockEntity::class);

        $this->assertFalse($map->remove(1));
        $this->assertTrue($map->add(new MockEntity(1)));
        $this->assertTrue($map->add(new MockEntity(2)));
        $this->assertTrue($map->remove(1));
        $this->assertEquals([2 => new MockEntity(2)], $map->getEntities());
    }
}