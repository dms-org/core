<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Language;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Language\Message;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MessageTest extends CmsTestCase
{
    public function testNew()
    {
        $message = new Message('abc.def', ['foo' => 'bar']);

        $this->assertSame(false, $message->hasNamespace());
        $this->assertSame(null, $message->getNamespace());
        $this->assertSame('abc.def', $message->getId());
        $this->assertSame(['foo' => 'bar'], $message->getParameters());
    }

    public function testWithNamespace()
    {
        $message = new Message('some-namespace::abc.def.ghi', ['quz' => 'baz']);

        $this->assertSame(true, $message->hasNamespace());
        $this->assertSame('some-namespace', $message->getNamespace());
        $this->assertSame('abc.def.ghi', $message->getId());
        $this->assertSame(['quz' => 'baz'], $message->getParameters());
    }
}