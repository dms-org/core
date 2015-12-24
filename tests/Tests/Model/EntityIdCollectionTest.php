<?php

namespace Dms\Core\Tests\Model;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\TypedCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdCollectionTest extends CmsTestCase
{
    public function testNewCollection()
    {
        $collection = new EntityIdCollection();

        $this->assertEquals(Type::int(), $collection->getElementType());
        $this->assertSame([], $collection->getAll());
    }

    public function testNewCollectionWithValues()
    {
        $collection = new EntityIdCollection([1, 2, 3, 5]);

        $this->assertSame([1, 2, 3, 5], $collection->getAll());
        $this->assertSame(4, $collection->count());

    }

    public function testClear()
    {
        $collection = new EntityIdCollection([1, 2, 3, 5]);

        $collection->clear();

        $this->assertSame([], $collection->getAll());
        $this->assertSame(0, $collection->count());
    }

    public function testProjectionReturnsTypedCollection()
    {
        $collection = new EntityIdCollection([1, 2, 3, 5]);

        $numbers = $collection->select(function ($id) {
            return $id * $id;
        });

        $this->assertInstanceOf(TypedCollection::class, $numbers);
        $this->assertNotInstanceOf(EntityIdCollection::class, $numbers);

        $this->assertSame([1, 4, 9, 25], $numbers->asArray());
    }
}