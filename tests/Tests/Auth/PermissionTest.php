<?php

namespace Dms\Core\Tests\Auth;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\InvalidArgumentException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PermissionTest extends CmsTestCase
{
    public function testNew()
    {
        $permission = new Permission('abc');

        $this->assertSame('abc', $permission->getName());
    }

    public function testNamedWithInstanceCache()
    {
        $one = Permission::named('123');
        $two = Permission::named('123');

        $this->assertSame('123', $one->getName());
        $this->assertSame('123', $two->getName());
        $this->assertSame($one, $two);
    }

    public function testEmptyName()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new Permission('');
    }

    public function testNamespace()
    {
        $permission = new Permission('abc');

        $namespaced      = $permission->inNamespace('qwerty');
        $namespacedAgain = $namespaced->inNamespace('123');

        $this->assertSame('abc', $permission->getName());
        $this->assertSame('qwerty.abc', $namespaced->getName());
        $this->assertSame('123.qwerty.abc', $namespacedAgain->getName());
    }
}