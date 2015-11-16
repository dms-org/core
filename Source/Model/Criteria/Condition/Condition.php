<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Model\ITypedObject;

/**
 * The condition base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Condition
{
    /**
     * @var callable
     */
    protected $arrayFilterCallable;

    /**
     * Condition constructor.
     */
    public function __construct()
    {
        $this->arrayFilterCallable = $this->makeArrayFilterCallable();
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