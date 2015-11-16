<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\IPartialLoadCriteria;

/**
 * The typed object criteria class with the properties of
 * the object to load.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PartialLoadCriteria extends Criteria implements IPartialLoadCriteria
{
    /**
     * @var NestedMember[]
     */
    private $nestedMembersToLoad = [];

    /**
     * Loads the supplied member.
     *
     * Example:
     * <code>
     * ->load('some.nested.property', 'array-index')
     * </code>
     *
     * @param string      $memberExpression
     * @param string|null $loadAsIndex
     *
     * @return static
     * @throws Exception\InvalidOperationException
     */
    final public function load($memberExpression, $loadAsIndex = null)
    {
        $this->nestedMembersToLoad[$loadAsIndex ?: $memberExpression] = $this->memberExpressionParser->parse($this->class,
                $memberExpression);

        return $this;
    }

    /**
     * Load all the supplied properties. Pass an array containing the properties
     * to load as the indexes and the value as the array index to load the property into.
     *
     * Example:
     * <code>
     * ->loadAll([
     *      'some.nested.property' => 'alias-index',
     *      'propertyIndexedByThisString',
     * ])
     * </code>
     *
     * @param string[] $memberExpressionIndexMap
     *
     * @return static
     * @throws Exception\InvalidOperationException
     */
    final public function loadAll(array $memberExpressionIndexMap)
    {
        foreach ($memberExpressionIndexMap as $memberExpression => $loadAsIndex) {
            if (is_int($memberExpression)) {
                $memberExpression = $loadAsIndex;
            }

            $this->nestedMembersToLoad[$loadAsIndex] = $this->memberExpressionParser->parse($this->class, $memberExpression);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    final public function getAliasNestedMemberMap()
    {
        return $this->nestedMembersToLoad;
    }

    /**
     * @inheritDoc
     */
    final public function getAliasNestedMemberStringMap()
    {
        $aliasMemberStringMap = [];

        foreach ($this->nestedMembersToLoad as $alias => $nestedMember) {
            $aliasMemberStringMap[$alias] = $nestedMember->asString();
        }

        return $aliasMemberStringMap;
    }

}