<?php

namespace Dms\Core\Tests\Form\Processor;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Processor\ArrayKeyHelper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayKeyHelperTest extends CmsTestCase
{
    public function testReplaceKeys()
    {
        $result = ArrayKeyHelper::mapArrayKeys(
                ['abc' => 'foo', 'bar' => '123'],
                ['abc' => 'def', 'bar' => 'baz']
        );

        $this->assertSame(['def' => 'foo', 'baz' => '123'], $result);
    }

    public function testReplaceKeyPartial()
    {
        $result = ArrayKeyHelper::mapArrayKeys(
                ['abc' => 'foo', 'bar' => '123'],
                ['abc' => 'def']
        );

        $this->assertSame(['def' => 'foo', 'bar' => '123'], $result);
    }
}