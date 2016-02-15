<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Dms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;

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
    public function buildCollection(array $children) : \Dms\Core\Model\ITypedCollection
    {
        return $this->reference->buildNewCollection($children);
    }
}