<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ReadModelMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation\RelationReadModelReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\EntityRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationObjectReference;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelationLoadingDefiner
{
    /**
     * @var ReadMapperDefinition
     */
    private $definition;

    /**
     * @var string|callable
     */
    private $propertyName;

    /**
     * @var callable
     */
    private $callback;

    /**
     * RelationLoadingDefiner constructor.
     *
     * @param ReadMapperDefinition $definition
     * @param string|callable      $propertyName
     * @param callable             $callback
     */
    public function __construct(ReadMapperDefinition $definition, $propertyName, callable $callback)
    {
        $this->definition   = $definition;
        $this->propertyName = $propertyName;
        $this->callback     = $callback;
    }

    /**
     * Defines to load the relation as the entity id.
     *
     * If it is a to-many relation a collection of ids will be loaded.
     *
     * @return void
     */
    public function asId()
    {
        call_user_func($this->callback, $this->propertyName, function (EntityRelation $relation) {
            /** @var IEntityMapper $entityMapper */
            $entityMapper = $relation->getMapper();
            if ($relation instanceof IToOneRelation) {
                return new ToOneRelationIdentityReference($entityMapper);
            } else {
                return new ToManyRelationIdentityReference($entityMapper);
            }
        });
    }

    /**
     * Defines to load the relation as the hydrated entity object.
     *
     * If it is a to-many relation a collection of entities will be loaded.
     *
     * @return void
     */
    public function asEntity()
    {
        call_user_func($this->callback, $this->propertyName, function (EntityRelation $relation) {
            /** @var IEntityMapper $entityMapper */
            $entityMapper = $relation->getMapper();
            if ($relation instanceof IToOneRelation) {
                return new ToOneRelationObjectReference($entityMapper);
            } else {
                return new ToManyRelationObjectReference($entityMapper);
            }
        });
    }

    /**
     * Defines to load the relation using the supplied read model definition.
     *
     * @param callable $readModelDefinition
     *
     * @return void
     */
    public function load(callable $readModelDefinition)
    {
        $readModelDefinition($this->definition);

        call_user_func($this->callback, $this->propertyName, function (IRelation $relation) {
            return new RelationReadModelReference(new ReadModelMapper($this->definition));
        });
    }
}