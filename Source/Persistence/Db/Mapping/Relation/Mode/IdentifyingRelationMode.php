<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The identifying relation mode class.
 *
 * This will delete all related entities if they have been removed
 * from the relationship.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IdentifyingRelationMode extends RelationMode
{
    public function syncInvalidatedRelationsQuery(
            PersistenceContext $context,
            Table $table,
            Column $foreignKey,
            Expr $invalidatedRelationExpr
    ) {
        $context->queue(
                Delete::from($table)
                        ->where($invalidatedRelationExpr)
        );
    }

    public function removeRelationsQuery(
            PersistenceContext $context,
            IObjectMapper $relatedMapper,
            Delete $parentDelete,
            Table $table,
            Column $foreignKey,
            Column $parentKeyColumn
    ) {
        /** @var Delete $deleteQuery */
        $deleteQuery = $this->foreignKeyJoin(
                $parentDelete->copy()->setTable($table),
                $parentDelete,
                $foreignKey,
                $parentKeyColumn
        );

        $relatedMapper->deleteFromQuery($context, $deleteQuery);
    }
}