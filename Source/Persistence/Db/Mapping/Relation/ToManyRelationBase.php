<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToManyRelationReference;

/**
 * The to many relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ToManyRelationBase extends EntityRelation implements IToManyRelation
{
    /**
     * @var IToManyRelationReference
     */
    protected $reference;

    /**
     * @inheritDoc
     */
    public function __construct(
            IToManyRelationReference $reference,
            IRelationMode $mode = null,
            $dependencyMode,
            array $relationshipTables = [],
            array $parentColumnsToLoad = []
    ) {
        parent::__construct($reference, $mode, $dependencyMode, $relationshipTables, $parentColumnsToLoad);
    }

    /**
     * @inheritDoc
     */
    public function buildCollection(array $children)
    {
        return $this->reference->buildNewCollection($children);
    }
}