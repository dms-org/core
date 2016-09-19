<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Condition\Condition;
use Dms\Core\Model\Object\FinalizedClassDefinition;

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
     * @var IMemberExpressionParser
     */
    protected $memberExpressionParser;

    /**
     * Criteria constructor.
     *
     * @param FinalizedClassDefinition     $class
     * @param IMemberExpressionParser|null $memberExpressionParser
     */
    public function __construct(FinalizedClassDefinition $class, IMemberExpressionParser $memberExpressionParser = null)
    {
        $this->class                  = $class;
        $this->memberExpressionParser = $memberExpressionParser ?: new MemberExpressionParser();
    }

    /**
     * {@inheritDoc}
     */
    final public function getClass() : FinalizedClassDefinition
    {
        return $this->class;
    }

    /**
     * @return IMemberExpressionParser
     */
    final public function getMemberExpressionParser() : IMemberExpressionParser
    {
        return $this->memberExpressionParser;
    }

    /**
     * {@inheritDoc}
     */
    final public function verifyOfClass(string $class)
    {
        if (!is_a($this->class->getClassName(), $class, true)) {
            throw Exception\TypeMismatchException::format(
                    'Criteria instance must be for class %s, %s given',
                    $class, $this->class->getClassName()
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function hasCondition() : bool
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