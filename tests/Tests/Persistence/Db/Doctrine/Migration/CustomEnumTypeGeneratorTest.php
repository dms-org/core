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

        $this->assertSame('CustomEnum__a__b__c', $typeName);
        $this->assertInstanceOf(BaseEnumType::class, $type);
        $this->assertSame('CustomEnum__a__b__c', $type->getName());
        $this->assertSame(['a', 'b', 'c'], $type->getValues());
        $this->assertSame('ENUM(\'a\',\'b\',\'c\')', $type->getSQLDeclaration([], new MySqlPlatform()));
        $this->assertSame($typeName, CustomEnumTypeGenerator::generate(['a', 'b', 'c']));
        $this->assertNotEquals($typeName, CustomEnumTypeGenerator::generate(['abc']));
    }

    public function testGenerateEnumWithOtherCharacters()
    {
        $typeName = CustomEnumTypeGenerator::generate(['a-b vsdvsd', 'c']);
        /** @var BaseEnumType $type */
        $type = Type::getType($typeName);

        $this->assertSame('CustomEnum__a_b_vsdvsd__c', $typeName);
        $this->assertInstanceOf(BaseEnumType::class, $type);
        $this->assertSame('CustomEnum__a_b_vsdvsd__c', $type->getName());
        $this->assertSame(['a-b vsdvsd', 'c'], $type->getValues());
        $this->assertSame('ENUM(\'a-b vsdvsd\',\'c\')', $type->getSQLDeclaration([], new MySqlPlatform()));
        $this->assertSame($typeName, CustomEnumTypeGenerator::generate(['a-b vsdvsd', 'c']));
        $this->assertNotEquals($typeName, CustomEnumTypeGenerator::generate(['abc']));
    }
}