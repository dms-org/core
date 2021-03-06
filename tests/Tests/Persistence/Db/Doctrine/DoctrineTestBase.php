<?php

namespace Dms\Core\Tests\Persistence\Db\Doctrine;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Tests\Bootstrap;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class DoctrineTestBase extends CmsTestCase
{
    public static function setUpBeforeClass(): void
    {
        Bootstrap::getComposer()->add('Doctrine\\Tests\\Mocks\\', Bootstrap::getVendorPath() . '/doctrine/dbal/tests/');
    }

    /**
     * @param string $expectedSql
     * @param string $actualSql
     * @param string $message
     *
     * @return void
     */
    protected function assertSqlSame($expectedSql, $actualSql, $message = '')
    {
        $expectedSql = preg_replace('/\s+/', '', $expectedSql);
        $actualSql   = preg_replace('/\s+/', '', $actualSql);

        $this->assertSame($expectedSql, $actualSql, $message);
    }
}