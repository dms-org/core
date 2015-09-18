<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\Condition\Condition;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The typed object criteria base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectCriteriaBase
{
    /**
     * @var FinalizedClassDefinition
     */
    protected $class;

    /**
     * @var Condition|null
     */
    protected $condition;

    /**
     * Criteria constructor.
     *
     * @param FinalizedClassDefinition $class
     */
    public function __construct(FinalizedClassDefinition $class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritDoc}
     */
    final public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    final public function verifyOfClass($class)
    {
        if ($this->class->getClassName() !== $class) {
            throw Exception\TypeMismatchException::format(
                    'Criteria instance must be for class %s, %s given',
                    $class, $this->class->getClassName()
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function hasCondition()
    {
        return $this->condition !== null;
    }

    /**
     * {@inheritDoc}
     */
    final public function getCondition()
    {
        return $this->condition;
    }
}