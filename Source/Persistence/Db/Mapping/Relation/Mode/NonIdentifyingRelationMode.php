<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The non-identifying relation mode class.
 *
 * This will set the foreign keys to null if the relationship
 * is removed.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NonIdentifyingRelationMode extends RelationMode
{
    public function syncInvalidatedRelationsQuery(
            PersistenceContext $context,
            Table $table,
            Column $foreignKey,
            Expr $invalidatedRelationExpr
    ) {
        $context->queue(
                Update::from($table)
                        ->set($foreignKey->getName(), Expr::idParam(null))
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
        $update = $this->foreignKeyJoin(
                Update::copyFrom($parentDelete)
                        ->setTable($table)
                        ->set($foreignKey->getName(), Expr::idParam(null)),
                $parentDelete,
                $foreignKey,
                $parentKeyColumn
        );

        $context->queue($update);
    }
}