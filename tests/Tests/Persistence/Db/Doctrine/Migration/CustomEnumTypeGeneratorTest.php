<?php

namespace Dms\Core\Tests\Persistence\Db\Doctrine\Migration;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Persistence\Db\Doctrine\Migration\CustomEnumTypeGenerator;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\BaseEnumType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomEnumTypeGeneratorTest extends CmsTestCase
{
    public function testGenerateEnum()
    {
        $typeName = CustomEnumTypeGenerator::generate(['a', 'b', 'c']);
        /** @var BaseEnumType $type */
        $type = Type::getType($typeName);

        $this->assertSame('enum(a,b,c)', $typeName);
        $this->assertInstanceOf(BaseEnumType::class, $type);
        $this->assertSame('enum(a,b,c)', $type->getName());
        $this->assertSame(['a', 'b', 'c'], $type->getValues());
        $this->assertSame('ENUM(\'a\',\'b\',\'c\')', $type->getSQLDeclaration([], new MySqlPlatform()));
        $this->assertSame($typeName, CustomEnumTypeGenerator::generate(['a', 'b', 'c']));
        $this->assertNotEquals($typeName, CustomEnumTypeGenerator::generate(['abc']));
    }
}