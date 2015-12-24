<?php

namespace Dms\Core\Tests\Util\Hashing;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Util\Hashing\IHashable;
use Dms\Core\Util\Hashing\ValueHasher;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueHasherTest extends CmsTestCase
{
    public function testHashUsesHashableInterface()
    {
        $hashable = $this->getMockForAbstractClass(IHashable::class);

        $hashable->expects($this->once())
            ->method('getObjectHash')
            ->willReturn('--hash--');

        $this->assertSame(get_class($hashable) . ':--hash--', ValueHasher::hash($hashable));
    }

    public function testHashFallsBackToSerialize()
    {
        $object = (object)['abc' => 123];

        $this->assertSame(serialize($object), ValueHasher::hash($object));
    }

    public function testScalars()
    {
        $this->assertSame(ValueHasher::hash(123), ValueHasher::hash(123));
        $this->assertSame(ValueHasher::hash('abc'), ValueHasher::hash('abc'));
        $this->assertSame(ValueHasher::hash(false), ValueHasher::hash(false));

        $this->assertNotEquals(ValueHasher::hash(123), ValueHasher::hash('123'));
        $this->assertNotEquals(ValueHasher::hash(123), ValueHasher::hash('abc'));
        $this->assertNotEquals(ValueHasher::hash(false), ValueHasher::hash(true));
    }
}