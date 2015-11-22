<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The relation interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IRelation
{
    const DEPENDENT_PARENTS = 'dependent-parents';
    const DEPENDENT_CHILDREN = 'dependent-children';

    /**
     * Gets a unique string for this relation.
     *
     * @return string
     */
    public function getIdString();

    /**
     * @return string
     */
    public function getDependencyMode();

    /**
     * Gets the columns on the parent table required to load the relation.
     *
     * @return string[]
     */
    public function getParentColumnsToLoad();

    /**
     * @return Table[]
     */
    public function getRelationshipTables();

    /**
     * @return IObjectMapper
     */
    public function getMapper();

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withEmbeddedColumnsPrefixedBy($prefix);

    /**
     * @param PersistenceContext $context
     * @param Delete             $parentDelete
     *
     * @return void
     */
    public function delete(PersistenceContext $context, Delete $parentDelete);
}