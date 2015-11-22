<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;

/**
 * The to many relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ToManyRelationBase extends EntityRelation implements ISeparateToManyTableRelation
{
    /**
     * @var IToManyRelationReference
     */
    protected $reference;

    /**
     * @inheritDoc
     */
    public function __construct(
            $idString,
            IToManyRelationReference $reference,
            IRelationMode $mode = null,
            $dependencyMode,
            array $relationshipTables = [],
            array $parentColumnsToLoad = []
    ) {
        parent::__construct($idString, $reference, $mode, $dependencyMode, $relationshipTables, $parentColumnsToLoad);
    }

    /**
     * @param LoadingContext    $context
     * @param ParentChildrenMap $map
     *
     * @return mixed
     */
    public function load(LoadingContext $context, ParentChildrenMap $map)
    {
        $select = $this->getRelationSelectFromParentRows($map, $parentIdColumnName);

        $this->loadFromSelect($context, $map, $select, $select->getTableAlias(), $parentIdColumnName);
    }

    /**
     * @inheritDoc
     */
    public function buildCollection(array $children)
    {
        return $this->reference->buildNewCollection($children);
    }
}