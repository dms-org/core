<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedObject;

/**
 * The condition base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Condition
{
    /**
     * @var Condition[]
     */
    protected $children;

    /**
     * @var callable
     */
    protected $arrayFilterCallable;

    /**
     * Condition constructor.
     *
     * @param Condition[] $children
     */
    public function __construct(array $children = [])
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'children', $children, __CLASS__);
        $this->children            = $children;
        $this->arrayFilterCallable = $this->makeArrayFilterCallable();
    }

    /**
     * @return Condition[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param callable $callable
     *
     * @return void
     */
    public function walkRecursive(callable $callable)
    {
        $callable($this);

        foreach ($this->children as $child) {
            $child->walkRecursive($callable);
        }
    }

    /**
     * Returns a callable that takes an array of objects and returns the
     * objects which satisfy the condition.
     *
     * NOTE: array keys are maintained.
     *
     * @return callable
     */
    abstract protected function makeArrayFilterCallable();

    /**
     * @return callable
     */
    final public function getFilterCallable()
    {
        $arrayCallback = $this->arrayFilterCallable;

        return function (ITypedObject $object) use ($arrayCallback) {
            return count($arrayCallback([$object])) === 1;
        };
    }

    /**
     * @return callable
     */
    final public function getArrayFilterCallable()
    {
        return $this->arrayFilterCallable;
    }
}