<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The relation mode interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IRelationMode
{
    /**
     * @param PersistenceContext $context
     * @param Table              $table
     * @param Column             $foreignKey
     * @param Expr               $invalidatedRelationExpr
     *
     * @return void
     */
    public function syncInvalidatedRelationsQuery(
            PersistenceContext $context,
            Table $table,
            Column $foreignKey,
            Expr $invalidatedRelationExpr
    );

    /**
     * @param PersistenceContext $context
     * @param IObjectMapper      $relatedMapper
     * @param Delete             $parentDelete
     * @param Table              $table
     * @param Column             $foreignKey
     * @param Column             $parentKeyColumn
     *
     */
    public function removeRelationsQuery(
            PersistenceContext $context,
            IObjectMapper $relatedMapper,
            Delete $parentDelete,
            Table $table,
            Column $foreignKey,
            Column $parentKeyColumn
    );
}