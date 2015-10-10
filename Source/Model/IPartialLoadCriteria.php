<?php

namespace Iddigital\Cms\Core\Model;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\NestedProperty;

/**
 * The object search criteria that also defines the properties of
 * the object to load.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IPartialLoadCriteria extends ICriteria
{
    /**
     * Gets the nested properties to load.
     *
     * Example:
     * <code>
     * [
     *      'alias' => [NestedProperty('some-property')]
     * ]
     * </code>
     *
     * @return NestedProperty[]
     */
    public function getAliasNestedPropertyMap();

    /**
     * Gets the nested property names to load.
     *
     * Example:
     * <code>
     * [
     *      'alias' => 'some.nested.property'
     * ]
     * </code>
     *
     * @return NestedProperty[]
     */
    public function getAliasNestedPropertyNameMap();
}
