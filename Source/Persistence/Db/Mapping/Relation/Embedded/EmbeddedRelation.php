<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Embedded;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Relation;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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
     * @param IEmbeddedObjectMapper $mapper
     * @param string                $dependencyMode
     * @param Table[]               $relationshipTables
     * @param array                 $parentColumnsToLoad
     */
    public function __construct(IEmbeddedObjectMapper $mapper, $dependencyMode, array $relationshipTables, array $parentColumnsToLoad)
    {
        parent::__construct($mapper, $dependencyMode, $relationshipTables, $parentColumnsToLoad);
    }

    /**
     * @return IEmbeddedObjectMapper
     */
    final public function getEmbeddedObjectMapper()
    {
        return $this->mapper;
    }
}