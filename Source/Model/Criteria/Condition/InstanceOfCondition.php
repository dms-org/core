<?php

namespace Iddigital\Cms\Core\Model\Criteria\Condition;

use Iddigital\Cms\Core\Model\ITypedObject;

/**
 * The instance of condition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InstanceOfCondition extends Condition
{
    /**
     * @var string
     */
    private $class;

    final public function __construct($class)
    {
        $this->class = $class;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    protected function makeFilterCallable()
    {
        $class = $this->class;

        return function (ITypedObject $object) use ($class) {
            return $object instanceof $class;
        };
    }
}