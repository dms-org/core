<?php declare(strict_types = 1);

namespace Dms\Core\Event;

/**
 * The namespaced event dispatcher class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NamespacedEventDispatcher extends EventDispatcher
{
    /**
     * @var string
     */
    private $eventNamespace;

    /**
     * @var IEventDispatcher
     */
    private $innerEventDispatcher;

    /**
     * NamespacedEventDispatcher constructor.
     *
     * @param string           $eventNamespace
     * @param IEventDispatcher $innerEventDispatcher
     */
    public function __construct(string $eventNamespace, IEventDispatcher $innerEventDispatcher)
    {
        $this->eventNamespace       = $eventNamespace;
        $this->innerEventDispatcher = $innerEventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function on(string $event, callable $listener)
    {
        $this->innerEventDispatcher->on($this->eventNamespace . $event, $listener);
    }

    /**
     * @inheritdoc
     */
    public function once(string $event, callable $listener)
    {
        $this->innerEventDispatcher->once($this->eventNamespace . $event, $listener);
    }

    /**
     * @inheritdoc
     */
    public function removeListener(string $event, callable $listener)
    {
        $this->innerEventDispatcher->removeListener($this->eventNamespace . $event, $listener);
    }

    /**
     * @inheritdoc
     */
    public function removeAllListeners(string $event = null)
    {
        $this->innerEventDispatcher->removeAllListeners($this->eventNamespace . ($event ?? '*'));
    }

    /**
     * @inheritdoc
     */
    public function getListeners(string $event) : array
    {
        return $this->innerEventDispatcher->getListeners($this->eventNamespace . $event);
    }

    /**
     * @inheritdoc
     */
    public function emit(string $event, ...$arguments)
    {
        $this->innerEventDispatcher->emit($this->eventNamespace . $event, ...$arguments);
    }

    /**
     * @inheritDoc
     */
    public function inNamespace(string $eventNamespace) : IEventDispatcher
    {
        return new self($eventNamespace . $this->eventNamespace, $this->innerEventDispatcher);
    }
}