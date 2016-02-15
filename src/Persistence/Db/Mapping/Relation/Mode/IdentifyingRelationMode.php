<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Mode;

use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;

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