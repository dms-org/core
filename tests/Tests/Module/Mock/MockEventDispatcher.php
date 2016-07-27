<?php

namespace Dms\Core\Tests\Module\Mock;

use Dms\Core\Event\EventDispatcher;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockEventDispatcher extends EventDispatcher
{
    /**
     * @var
     */
    protected $emittedEvents = [];

    /**
     * @return array
     */
    public function getEmittedEvents() : array
    {
        return $this->emittedEvents;
    }

    /**
     * @inheritDoc
     */
    public function on(string $event, callable $listener)
    {
    }

    /**
     * @inheritDoc
     */
    public function once(string $event, callable $listener)
    {
    }

    /**
     * @inheritDoc
     */
    public function removeListener(string $event, callable $listener)
    {
    }

    /**
     * @inheritDoc
     */
    public function removeAllListeners(string $event = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function getListeners(string $event) : array
    {
    }

    /**
     * @inheritDoc
     */
    public function emit(string $event, ...$arguments)
    {
        $this->emittedEvents[] = array_merge([$event], $arguments);
    }
}