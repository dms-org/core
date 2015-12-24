<?php

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The member expression tree node.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberExpressionNode
{
    /**
     * @var IMemberExpression
     */
    protected $memberExpression;

    /**
     * @var MemberExpressionNode[]
     */
    protected $children;

    /**
     * @var string[]
     */
    protected $aliases;

    /**
     * MemberExpressionNode constructor.
     *
     * @param IMemberExpression      $memberExpression
     * @param MemberExpressionNode[] $children
     * @param string[]               $aliases
     */
    public function __construct(IMemberExpression $memberExpression, array $children, array $aliases)
    {
        InvalidArgumentException::verify($children || $aliases, 'children or alias must be supplied');
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'children', $children, __CLASS__);

        $this->memberExpression = $memberExpression;
        $this->children         = $children;
        $this->aliases          = $aliases;
    }

    /**
     * @return IMemberExpression
     */
    public function getMemberExpression()
    {
        return $this->memberExpression;
    }

    /**
     * @return MemberExpressionNode[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return string[]
     */
    public function getLoadAliases()
    {
        return $this->aliases;
    }
}