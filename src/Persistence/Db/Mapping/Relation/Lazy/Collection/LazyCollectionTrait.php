<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection;

use Pinq\Iterators\Generators\GeneratorScheme;
use Snapfile\Domain\Entities\User\ProfileData;
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
     * @var array
     */
    protected $lazyMetadata = [];

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
             * @var array
             */
            protected $elementsCache;

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

                if ($this->elementsCache === null) {
                    $this->elementsCache = call_user_func($this->loadElementArrayCallback);
                }

                return new \ArrayIterator($this->elementsCache);
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

    /**
     * @return array
     */
    public function getLazyMetadata() : array
    {
        return $this->lazyMetadata;
    }

    /**
     * @param array $values
     *
     * @return void
     */
    public function appendLazyMetadata(array $values)
    {
        $this->lazyMetadata = $values + $this->lazyMetadata;
    }
}