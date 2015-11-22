<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

/**
 * The to many relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ToOneRelationBase extends EntityRelation implements ISeparateToOneTableRelation
{
    /**
     * @var IToOneRelationReference
     */
    protected $reference;

    /**
     * @inheritDoc
     */
    public function __construct(
            $idString,
            IToOneRelationReference $reference,
            IRelationMode $mode = null,
            $dependencyMode,
            array $relationshipTables = [],
            array $parentColumnsToLoad = []
    ) {
        parent::__construct($idString, $reference, $mode, $dependencyMode, $relationshipTables, $parentColumnsToLoad);
    }

    /**
     * @param LoadingContext $context
     * @param ParentChildMap $map
     *
     * @return void
     */
    public function load(LoadingContext $context, ParentChildMap $map)
    {
        $select = $this->getRelationSelectFromParentRows($map, $parentIdColumnName);

        $this->loadFromSelect($context, $map, $select, $select->getTableAlias(), $parentIdColumnName);
    }

    /**
     * @inheritDoc
     */
    public function getRelationSubSelect(Select $outerSelect, $parentTableAlias)
    {
        $subSelect = $outerSelect->buildSubSelect($this->relatedTable);

        return $subSelect
                ->where($this->getRelationJoinCondition($parentTableAlias, $subSelect->getTableAlias()));
    }
}