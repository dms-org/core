<?php

namespace Dms\Core\Model\Criteria\Condition;

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

    /**
     * @inheritdoc
     */
    protected function makeArrayFilterCallable()
    {
        $class = $this->class;

        return function (array $objects) use ($class) {
            $filtered = [];

            foreach ($objects as $key => $object) {
                if ($object instanceof $class) {
                    $filtered[$key] = $object;
                }
            }

            return $filtered;
        };
    }
}