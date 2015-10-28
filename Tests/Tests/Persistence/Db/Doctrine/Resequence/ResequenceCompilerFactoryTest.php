<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Doctrine\Resequence;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\DoctrineExpressionCompiler;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\DoctrinePlatform;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\Resequence\DefaultResequenceCompiler;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\Resequence\MysqlResequenceCompiler;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\Resequence\ResequenceCompilerFactory;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ResequenceCompilerFactoryTest extends CmsTestCase
{
    /**
     * @param AbstractPlatform $doctrinePlatform
     *
     * @return DoctrinePlatform|\PHPUnit_Framework_MockObject_MockObject
     */
    public function mockPlatform(AbstractPlatform $doctrinePlatform)
    {
        $platform = $this->getMockBuilder(DoctrinePlatform::class)
                ->disableOriginalConstructor()
                ->getMock();

        $platform
                ->expects($this->once())
                ->method('getExpressionCompiler')
                ->willReturn(new DoctrineExpressionCompiler($platform));

        $platform
                ->expects($this->once())
                ->method('getDoctrinePlatform')
                ->willReturn($doctrinePlatform);

        return $platform;
    }

    public function testMysql()
    {
        $platform = $this->mockPlatform(new MySqlPlatform());

        $this->assertInstanceOf(MysqlResequenceCompiler::class, ResequenceCompilerFactory::buildFor($platform));
    }

    public function testOther()
    {
        $platform = $this->mockPlatform(new SqlitePlatform());

        $this->assertInstanceOf(DefaultResequenceCompiler::class, ResequenceCompilerFactory::buildFor($platform));
    }
}