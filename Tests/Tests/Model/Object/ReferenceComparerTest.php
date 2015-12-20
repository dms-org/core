<?php

namespace Dms\Core\Tests\Model;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Object\ReferenceComparer;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReferenceComparerTest extends CmsTestCase
{
    public function testNonIdenticalVariables()
    {
        $var1 = 1;
        $var2 = '1';

        $this->assertFalse(ReferenceComparer::areEqual($var1, $var2));
        $this->assertSame(1, $var1);
        $this->assertSame('1', $var2);
    }

    public function testIdenticalVariables()
    {
        $var1 = 1;
        $var2 = $var1;

        $this->assertFalse(ReferenceComparer::areEqual($var1, $var2));
        $this->assertSame(1, $var1);
        $this->assertSame(1, $var2);
    }

    public function testReferencedVariables()
    {
        $var1 = 1;
        $var2 =& $var1;

        $this->assertTrue(ReferenceComparer::areEqual($var1, $var2));
        $this->assertSame(1, $var1);
        $this->assertSame(1, $var2);
        // Should maintain reference
        $this->assertTrue(ReferenceComparer::areEqual($var1, $var2));
    }
}