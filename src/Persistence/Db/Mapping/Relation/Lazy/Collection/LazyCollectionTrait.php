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
     * @param callable $loadElementArrayCallback
     */
    protected function setLazyLoadingCallback(callable $loadElementArrayCallback)
    {
        $this->elements = new class($loadElementArrayCallback) implements \IteratorAggregate
        {
            /**
             * @var callable
             */
            protected $loadElementArrayCallback;

            /**
             * @param callable $loadElementArrayCallback
             */
            public function __construct(callable $loadElementArrayCallback)
            {
                $this->loadElementArrayCallback = $loadElementArrayCallback;
            }

            public function getIterator()
            {
                return new \ArrayIterator(call_user_func($this->loadElementArrayCallback));
            }
        };
    }

    abstract protected function verifyElement($element);
}