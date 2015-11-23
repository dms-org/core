<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\ILoadCriteria;

/**
 * The typed object criteria class with the properties of
 * the object to load.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadCriteria extends Criteria implements ILoadCriteria
{
    /**
     * @var NestedMember[]
     */
    private $nestedMembersToLoad = [];

    /**
     * @inheritdoc
     */
    final public function load($memberExpression, $loadAsIndex = null)
    {
        $loadAsIndex                             = $loadAsIndex ?: $memberExpression;
        $this->nestedMembersToLoad[$loadAsIndex] = $this->memberExpressionParser->parse($this->class, $memberExpression);

        return $this;
    }

    /**
     * @inheritdoc
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

    /**
     * @inheritdoc
     */
    public function getAliasMemberTree()
    {
        return MemberExpressionTree::buildTree($this->nestedMembersToLoad);
    }
}