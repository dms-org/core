<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

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
    protected $filterCallable;

    /**
     * Condition constructor.
     */
    public function __construct()
    {
        $this->filterCallable = $this->makeFilterCallable();
    }

    /**
     * Returns a callable that takes an object and returns a boolean
     * whether the object passes the condition.
     *
     * @return callable
     */
    abstract protected function makeFilterCallable();

    /**
     * @return callable
     */
    final public function getFilterCallable()
    {
        return $this->filterCallable;
    }
}