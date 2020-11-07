<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Event;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Event\IEventDispatcher;
use Dms\Core\Event\NamespacedEventDispatcher;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NamespacedEventDispatcherTest extends CmsTestCase
{
    /**
     * @var NamespacedEventDispatcher
     */
    protected $dispatcher;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $innerEventDispatcher;

    public function setUp(): void
    {
        $this->innerEventDispatcher = $this->getMockForAbstractClass(IEventDispatcher::class);
        $this->dispatcher           = new NamespacedEventDispatcher('namespace.', $this->innerEventDispatcher);
    }

    public function testOnEvent()
    {
        $this->innerEventDispatcher
            ->expects(self::once())
            ->method('on')
            ->with('namespace.event', 'strlen');

        $this->dispatcher->on('event', 'strlen');
    }

    public function testOnceEvent()
    {
        $this->innerEventDispatcher
            ->expects(self::once())
            ->method('once')
            ->with('namespace.event', 'strlen');

        $this->dispatcher->once('event', 'strlen');
    }

    public function testRemoveListenerEvent()
    {
        $this->innerEventDispatcher
            ->expects(self::once())
            ->method('removeListener')
            ->with('namespace.event', 'strlen');

        $this->dispatcher->removeListener('event', 'strlen');
    }

    public function testRemoveAllListenersEvent()
    {
        $this->innerEventDispatcher
            ->expects(self::once())
            ->method('removeAllListeners')
            ->with('namespace.event');

        $this->dispatcher->removeAllListeners('event');
    }

    public function testGetListeners()
    {
        $this->innerEventDispatcher
            ->expects(self::once())
            ->method('getListeners')
            ->with('namespace.event')
            ->willReturn(['a']);

        $this->assertSame(['a'], $this->dispatcher->getListeners('event'));
    }

    public function testEmit()
    {
        $this->innerEventDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with('namespace.event', 1, 2, 3);

        $this->dispatcher->emit('event', 1, 2, 3);
    }

    public function testInSubNamespace()
    {
        $this->innerEventDispatcher
            ->expects(self::once())
            ->method('emit')
            ->with('new-namespace.namespace.event', 1, 2, 3);

        $this->dispatcher
            ->inNamespace('new-namespace.')
            ->emit('event', 1, 2, 3);
    }
}