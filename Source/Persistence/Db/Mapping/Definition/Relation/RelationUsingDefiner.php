<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;

/**
 * The relation using definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelationUsingDefiner
{
    /**
     * @var IOrm
     */
    private $orm;

    /**
     * @var callable
     */
    private $callback;

    /**
     * TypeTableDefiner constructor.
     *
     * @param IOrm     $orm
     * @param callable $callback
     */
    public function __construct(IOrm $orm, callable $callback)
    {
        $this->orm      = $orm;
        $this->callback = $callback;
    }

    /**
     * Sets the relation to use the supplied mapper.
     *
     * @param IEntityMapper $mapper
     *
     * @return RelationDefiner
     */
    public function using(IEntityMapper $mapper)
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
    public function to($entityType, $tableName = null)
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
}