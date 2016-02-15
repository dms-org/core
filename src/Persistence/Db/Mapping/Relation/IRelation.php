<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Schema\Table;

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
    public function getIdString() : string;

    /**
     * @return string
     */
    public function getDependencyMode() : string;

    /**
     * Gets the columns on the parent table required to load the relation.
     *
     * @return string[]
     */
    public function getParentColumnsToLoad() : array;

    /**
     * @return Table[]
     */
    public function getRelationshipTables() : array;

    /**
     * @return IObjectMapper
     */
    public function getMapper() : \Dms\Core\Persistence\Db\Mapping\IObjectMapper;

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withEmbeddedColumnsPrefixedBy(string $prefix);

    /**
     * @param PersistenceContext $context
     * @param Delete             $parentDelete
     *
     * @return void
     */
    public function delete(PersistenceContext $context, Delete $parentDelete);
}