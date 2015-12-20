<?php

namespace Dms\Core\Persistence\Db\Mapping\Relation\Embedded;

use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Relation;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The embedded  relation class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EmbeddedRelation extends Relation implements IRelation
{
    /**
     * @var IEmbeddedObjectMapper
     */
    protected $mapper;

    /**
     * EmbeddedRelation constructor.
     *
     * @param string $idString
     * @param IEmbeddedObjectMapper $mapper
     * @param string $dependencyMode
     * @param Table[] $relationshipTables
     * @param array $parentColumnsToLoad
     */
    public function __construct(
            $idString,
            IEmbeddedObjectMapper $mapper,
            $dependencyMode,
            array $relationshipTables,
            array $parentColumnsToLoad
    ) {
        parent::__construct($idString, $mapper, $dependencyMode, $relationshipTables, $parentColumnsToLoad);
    }

    /**
     * @return IEmbeddedObjectMapper
     */
    final public function getEmbeddedObjectMapper()
    {
        return $this->mapper;
    }
}