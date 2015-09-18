<?php

namespace Iddigital\Cms\Core\Tests\Model;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Type\ArrayType;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\CollectionType;
use Iddigital\Cms\Core\Model\Type\MixedType;
use Iddigital\Cms\Core\Model\Type\NullType;
use Iddigital\Cms\Core\Model\Type\ObjectType;
use Iddigital\Cms\Core\Model\Type\ScalarType;
use Iddigital\Cms\Core\Model\Type\UnionType;
use Iddigital\Cms\Core\Model\TypedCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypeBuilderTest extends CmsTestCase
{
    public function testMixed()
    {
        /** @var MixedType $type */
        $type = Type::mixed();

        $this->assertInstanceOf(MixedType::class, $type);
    }

    public function testString()
    {
        /** @var ScalarType $type */
        $type = Type::string();

        $this->assertInstanceOf(ScalarType::class, $type);
        $this->assertSame(ScalarType::STRING, $type->getType());
    }

    public function testInt()
    {
        /** @var ScalarType $type */
        $type = Type::int();

        $this->assertInstanceOf(ScalarType::class, $type);
        $this->assertSame(ScalarType::INT, $type->getType());
    }

    public function testBool()
    {
        /** @var ScalarType $type */
        $type = Type::bool();

        $this->assertInstanceOf(ScalarType::class, $type);
        $this->assertSame(ScalarType::BOOL, $type->getType());
    }

    public function testFloat()
    {
        /** @var ScalarType $type */
        $type = Type::float();

        $this->assertInstanceOf(ScalarType::class, $type);
        $this->assertSame(ScalarType::FLOAT, $type->getType());
    }

    public function testNull()
    {
        /** @var NullType $type */
        $type = Type::null();

        $this->assertInstanceOf(NullType::class, $type);
    }

    public function testObject()
    {
        /** @var ObjectType $type */
        $type = Type::object();

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertNull($type->getClass());
    }

    public function testObjectWithClass()
    {
        /** @var ObjectType $type */
        $type = Type::object(\stdClass::class);

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertSame('stdClass', $type->getClass());
    }

    public function testCollectionOfType()
    {
        /** @var CollectionType $type */
        $type = Type::collectionOf(Type::object(\DateTime::class));

        $this->assertInstanceOf(CollectionType::class, $type);
        $this->assertEquals(Type::object(\DateTime::class), $type->getElementType());
    }

    public function testArray()
    {
        /** @var ArrayType $type */
        $type = Type::arrayOf(Type::mixed());

        $this->assertInstanceOf(ArrayType::class, $type);
        $this->assertEquals(Type::mixed(), $type->getElementType());
    }

    public function testNumber()
    {
        /** @var UnionType $type */
        $type = Type::number();

        $this->assertInstanceOf(UnionType::class, $type);
        $this->assertEquals([Type::int(), Type::float()], array_values($type->getTypes()));
    }

    public function testFromValue()
    {
        $this->assertEquals(Type::string(), Type::from(''));
        $this->assertEquals(Type::null(), Type::from(null));
        $this->assertEquals(Type::int(), Type::from(0));
        $this->assertEquals(Type::float(), Type::from(0.0));
        $this->assertEquals(Type::bool(), Type::from(true));
        $this->assertEquals(Type::arrayOf(Type::mixed()), Type::from([]));
        $this->assertEquals(Type::object(\stdClass::class), Type::from(new \stdClass()));
        $this->assertEquals(Type::collectionOf(Type::int()), Type::from(new TypedCollection(Type::int())));
    }
}