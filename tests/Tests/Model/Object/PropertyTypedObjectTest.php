<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Model\Object\Fixtures\TestPropertyTypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyTypedObjectTest extends CmsTestCase
{
    public function testInfersTypesFromTypeDeclarations()
    {
<<<<<<< HEAD
        if (version_compare(PHP_VERSION, '7.4.0', '<=')) {
            $this->expectNotToPerformAssertions();
            return;
        }

=======
>>>>>>> d579d69189e4d5fc2cf1b45a4b326a0b25a633a7
        $this->assertEquals([
            'string' => Type::string(),
            'int' => Type::int(),
            'float' => Type::float(),
            'bool' => Type::bool(),
            'array' => Type::arrayOf(Type::mixed()),
            'object' => Type::object(),
            'iterable' => Type::iterable(),
            'arrayObject' => Type::object(\ArrayObject::class),
            'nullableString' => Type::string()->nullable(),
            'nullableArrayObject' => Type::object(\ArrayObject::class)->nullable(),
        ], TestPropertyTypedObject::definition()->getPropertyTypeMap());
    }
}
