<?php declare(strict_types = 1);

namespace Dms\Core\Event;

/**
 * The event dispatcher interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IEventDispatcher
{
    /**
     * Registers an event listener
     *
     * @param string   $event
     * @param callable $listener
     *
     * @return void
     */
    public function on(string $event, callable $listener);

    /**
     * Registers an event listener that will only be called once.
     *
     * @param string   $event
     * @param callable $listener
     *
     * @return void
     */
    public function once(string $event, callable $listener);

    /**
     * Removes the supplied listener.
     *
     * @param string   $event
     * @param callable $listener
     *
     * @return void
     */
    public function removeListener(string $event, callable $listener);

    /**
     * Removes the listeners for the supplied event or all events if null.
     *
     * @param string|null $event
     *
     * @return void
     */
    public function removeAllListeners(string $event = null);

    /**
     * Gets all listeners for the supplied event.
     *
     * @param string $event
     *
     * @return callable[]
     */
    public function getListeners(string $event) : array;

    /**
     * Emits the supplied event with the supplied arguments
     *
     * @param string $event
     * @param array  ...$arguments
     *
     * @return void
     */
    public function emit(string $event, ...$arguments);

    /**
     * Gets an event dispatcher in the supplied namespace.
     * 
     * @param string $eventNamespace
     *
     * @return IEventDispatcher
     */
    public function inNamespace(string $eventNamespace) : IEventDispatcher;
}