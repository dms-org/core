<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Pinq\Iterators\Generators\GeneratorScheme;
use Traversable;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
trait LazyCollectionTrait
{
    /**
     * @var \Traversable
     */
    protected $elements;

    /**
     * @var bool
     */
    protected $hasLoadedElements = false;

    /**
     * @param callable $loadElementArrayCallback
     */
    protected function setLazyLoadingCallback(callable $loadElementArrayCallback)
    {
        $this->elements = new class($this->hasLoadedElements, $loadElementArrayCallback) implements \IteratorAggregate
        {
            /**
             * @var bool
             */
            protected $hasLoadedElements;

            /**
             * @var callable
             */
            protected $loadElementArrayCallback;

            /**
             * @param bool     $hasLoadedElements
             * @param callable $loadElementArrayCallback
             */
            public function __construct(bool &$hasLoadedElements, callable $loadElementArrayCallback)
            {
                $this->hasLoadedElements        =& $hasLoadedElements;
                $this->loadElementArrayCallback = $loadElementArrayCallback;
            }

            public function getIterator()
            {
                $this->hasLoadedElements = true;

                return new \ArrayIterator(call_user_func($this->loadElementArrayCallback));
            }
        };
    }

    abstract protected function verifyElement($element);

    /**
     * @return bool
     */
    public function hasLoadedElements() : bool
    {
        return $this->hasLoadedElements;
    }
}