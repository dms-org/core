<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\MemberExpressionNode;
use Dms\Core\Model\Criteria\NestedMember;

/**
 * The object search criteria that also defines the members
 * of the class to load as an array.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ILoadCriteria extends ICriteria
{
    /**
     * Gets the nested members to load.
     *
     * Example:
     * <code>
     * [
     *      'alias' => [NestedMember('some-expression')]
     * ]
     * </code>
     *
     * @return NestedMember[]
     */
    public function getAliasNestedMemberMap() : array;

    /**
     * Gets the nested member strings names to load.
     *
     * Example:
     * <code>
     * [
     *      'alias'     => 'some.nested.property',
     *      'aggregate' => 'some.nested.collection.count()',
     * ]
     * </code>
     *
     * @return string[]
     */
    public function getAliasNestedMemberStringMap() : array;

    /**
     * Returns a tree structure containing a tree of
     * member expressions to load.
     *
     * Example:
     * <code>
     * // For
     * [
     *      'alias'     => 'some.property',
     *      'aggregate' => 'some.collection.count()',
     *      'val'       => 'value',
     * ]
     * // Will return (pseudo-code)
     * [
     *      MemberExpressionNode(
     *          PropertyMemberExpression('some'),
     *          children => [
     *              MemberExpressionNode(
     *                  PropertyMemberExpression('property'),
     *                  aliases => ['alias']
     *              ),
     *              MemberExpressionNode(
     *                  PropertyMemberExpression('property'),
     *                  children => [
     *                       MemberExpressionNode(
     *                          CollectionCountMethodExpression('),
     *                          aliases => ['count']
     *                       )
     *                  ]
     *              )
     *      ),
     *      MemberExpressionNode(
     *          PropertyMemberExpression('value'),
     *          aliases => ['val']
     *      )
     * ]
     * </code>
     *
     * @see MemberExpressionNode
     *
     * @return MemberExpressionNode[]
     */
    public function getAliasMemberTree() : array;
}
