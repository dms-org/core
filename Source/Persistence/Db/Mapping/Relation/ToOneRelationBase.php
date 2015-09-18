<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\IToOneRelationReference;

/**
 * The to many relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ToOneRelationBase extends EntityRelation implements IToOneRelation
{
    /**
     * @var IToOneRelationReference
     */
    protected $reference;

    /**
     * @inheritDoc
     */
    public function __construct(
            IToOneRelationReference $reference,
            IRelationMode $mode = null,
            $dependencyMode,
            array $relationshipTables = [],
            array $parentColumnsToLoad = []
    ) {
        parent::__construct($reference, $mode, $dependencyMode, $relationshipTables, $parentColumnsToLoad);
    }
}