<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;

/**
 * The object search criteria that also defines the properties of
 * the object to load.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IPartialLoadCriteria extends ICriteria
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
    public function getAliasNestedMemberMap();

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
    public function getAliasNestedMemberStringMap();
}
