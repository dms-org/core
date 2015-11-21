<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;

/**
 * The member expression tree builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberExpressionTree
{
    /**
     * @param NestedMember[] $aliasMemberMap
     *
     * @return MemberExpressionNode[]
     */
    public static function buildTree(array $aliasMemberMap)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'aliasMemberMap', $aliasMemberMap, NestedMember::class);

        $arrayTree     = [];
        $hashMemberMap = [];

        foreach ($aliasMemberMap as $alias => $nestedMember) {
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

        return self::buildMemberNodeFromTree('', $arrayTree, $hashMemberMap);
    }

    protected static function buildMemberNodeFromTree($currentMemberHash, array $aliasTree, array $hashMemberMap)
    {
        $aliases   = [];
        $children = [];

        foreach ($aliasTree as $memberString => $node) {
            if (is_string($node)) {
                // It is an alias
                $aliases[] = $node;
            } elseif (is_array($node)) {
                // It is child nodes
                $children[] = self::buildMemberNodeFromTree(
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