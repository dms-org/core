<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation\Embedded;

use Dms\Core\Model\Type\IType;
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
     * @param string                $idString
     * @param IType                 $valueType
     * @param IEmbeddedObjectMapper $mapper
     * @param string                $dependencyMode
     * @param Table[]               $relationshipTables
     * @param array                 $parentColumnsToLoad
     */
    public function __construct(
            string $idString,
            IType $valueType,
            IEmbeddedObjectMapper $mapper,
            string $dependencyMode,
            array $relationshipTables,
            array $parentColumnsToLoad
    ) {
        parent::__construct($idString, $valueType, $mapper, $dependencyMode, $relationshipTables, $parentColumnsToLoad);
    }

    /**
     * @return IEmbeddedObjectMapper
     */
    final public function getEmbeddedObjectMapper() : IEmbeddedObjectMapper
    {
        return $this->mapper;
    }
}