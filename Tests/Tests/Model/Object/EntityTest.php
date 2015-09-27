<?php

namespace Iddigital\Cms\Core\Tests\Model\Object;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\IEntityCollection;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\BlankEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityTest extends TypedObjectTest
{
    /**
     * @var BlankEntity
     */
    protected $object;

    /**
     * @return BlankEntity
     */
    protected function buildObject()
    {
        return new BlankEntity();
    }

    public function testNotAbstract()
    {
        $this->assertFalse(BlankEntity::definition()->isAbstract());
    }

    public function testGetDefaultId()
    {
        $this->assertNull($this->object->getId());
        $this->assertFalse($this->object->hasId());
    }

    public function testSetId()
    {
        $this->object->setId(12);

        $this->assertSame(12, $this->object->getId());
        $this->assertTrue($this->object->hasId());
    }

    public function testSetIdToNullThrows()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->object->setId(null);
    }

    public function testSetIdAfterItIsSetThrows()
    {
        $this->setExpectedException(InvalidOperationException::class);
        $this->object->setId(12);
        $this->object->setId(14);
    }

    public function testBuildWithId()
    {
        $entity = new BlankEntity(42);
        $this->assertSame(42, $entity->getId());
    }

    public function testToArray()
    {
        $this->assertSame(['id' => null], $this->object->toArray());
        $this->object->setId(14);
        $this->assertSame(['id' => 14], $this->object->toArray());
    }

    public function testHydrateId()
    {
        $entity = BlankEntity::hydrateNew(['id' => 412]);
        $this->assertSame(412, $entity->getId());
    }

    public function testEntityCollection()
    {
        $collection = BlankEntity::collection([new BlankEntity(42)]);

        $this->assertInstanceOf(IEntityCollection::class, $collection);
        $this->assertSame(BlankEntity::class, $collection->getEntityType());
        $this->assertSame(BlankEntity::class, $collection->getObjectType());
        $this->assertEquals(Type::object(BlankEntity::class), $collection->getElementType());
        $this->assertCount(1, $collection);
    }
}