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
        $arrayTree     = [];
        $hashMemberMap = [];

        foreach ($this->nestedMembersToLoad as $alias => $nestedMember) {
            $currentMemberHash = '';
            $currentNode       =& $arrayTree;

            foreach ($nestedMember->getParts() as $part) {
                $memberString = $part->asString();
                $currentMemberHash .= '||' . $memberString;

                $currentNode =& $currentNode[$memberString];
                $hashMemberMap[$currentMemberHash] = $part;
            }

            $currentNode[] = $alias;
        }

        return $this->buildMemberNodeFromTree('', $arrayTree, $hashMemberMap);
    }

    protected function buildMemberNodeFromTree($currentMemberHash, array $aliasTree, array $hashMemberMap)
    {
        $aliases   = [];
        $children = [];

        foreach ($aliasTree as $memberString => $node) {
            if (is_string($node)) {
                // It is an alias
                $aliases[] = $node;
            } elseif (is_array($node)) {
                // It is child nodes
                $children[] = $this->buildMemberNodeFromTree(
                        $currentMemberHash . '||' . $memberString,
                        $node,
                        $hashMemberMap
                );
            }
        }

        if ($currentMemberHash === '') {
            // Root node, just need node array
            return $children;
        }

        return new MemberExpressionNode(
                $hashMemberMap[$currentMemberHash],
                $children,
                $aliases
        );
    }
}