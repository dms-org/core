<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;

/**
 * The relation using definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelationUsingDefiner
{
    /**
     * @var MapperDefinition
     */
    private $definition;

    /**
     * @var IOrm
     */
    private $orm;

    /**
     * @var IAccessor
     */
    private $accessor;

    /**
     * @var callable
     */
    private $callback;

    /**
     * RelationUsingDefiner constructor.
     *
     * @param MapperDefinition $definition
     * @param IOrm             $orm
     * @param IAccessor        $accessor
     * @param callable         $callback
     */
    public function __construct(MapperDefinition $definition, IOrm $orm, IAccessor $accessor, callable $callback)
    {
        $this->definition = $definition;
        $this->orm        = $orm;
        $this->callback   = $callback;
        $this->accessor   = $accessor;
    }

    /**
     * Sets the relation to use the supplied mapper.
     *
     * @param IEntityMapper $mapper
     *
     * @return RelationDefiner
     */
    public function using(IEntityMapper $mapper) : RelationDefiner
    {
        return new RelationDefiner($this->callback, function () use ($mapper) {
            return $mapper;
        });
    }

    /**
     * Sets the relation to use the mapper of the
     * supplied entity type.
     *
     * If the related entity is mapped to multiple tables
     * the table name must be specified.
     *
     * @param string      $entityType
     * @param string|null $tableName
     *
     * @return RelationDefiner
     */
    public function to(string $entityType, string $tableName = null) : RelationDefiner
    {
        return new RelationDefiner($this->callback, function () use ($entityType, $tableName) {
            return $this->orm->getEntityMapper($entityType, $tableName);
        });
    }

    /**
     * Sets the relation to use the supplied relation instance.
     *
     * @param IRelation $relation
     *
     * @return void
     */
    public function asCustom(IRelation $relation)
    {
        call_user_func($this->callback, function () use ($relation) {
            return $relation;
        });
    }

    /**
     * Defines the relation as an embedded (value object) relation.
     *
     * @return EmbeddedRelationTypeDefiner
     */
    public function asEmbedded() : EmbeddedRelationTypeDefiner
    {
        return new EmbeddedRelationTypeDefiner($this->definition, $this->orm, $this->accessor, $this->callback);
    }
}