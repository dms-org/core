<?php

namespace Iddigital\Cms\Core\Tests\Util\Hashing;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Util\Hashing\IHashable;
use Iddigital\Cms\Core\Util\Hashing\ObjectHasher;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectHasherTest extends CmsTestCase
{
    public function testHashUsesHashableInterface()
    {
        $hashable = $this->getMockForAbstractClass(IHashable::class);

        $hashable->expects($this->once())
            ->method('getObjectHash')
            ->willReturn('--hash--');

        $this->assertSame(get_class($hashable) . ':--hash--', ObjectHasher::hash($hashable));
    }

    public function testHashFallsBackToSerialize()
    {
        $object = (object)['abc' => 123];

        $this->assertSame(serialize($object), ObjectHasher::hash($object));
    }

    public function testInvalidArgument()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        ObjectHasher::hash(123);
    }
}