<?php declare(strict_types = 1);

namespace Dms\Core\Event;

/**
 * The base event dispatcher class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EventDispatcher implements IEventDispatcher
{
    /**
     * @inheritDoc
     */
    public function inNamespace(string $eventNamespace) : IEventDispatcher
    {
        return new NamespacedEventDispatcher($eventNamespace, $this);
    }
}