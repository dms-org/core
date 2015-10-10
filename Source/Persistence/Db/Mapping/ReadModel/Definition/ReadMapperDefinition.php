<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Model\IReadModel;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\IncompleteMapperDefinitionException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\EmbeddedMapperProxy;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\GenericReadModelMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;

/**
 * The read model mapper definition class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadMapperDefinition
{
    /**
     * @var IOrm
     */
    protected $orm;

    /**
     * @var FinalizedClassDefinition
     */
    protected $class;

    /**
     * @var IObjectMapper
     */
    protected $mapper;

    /**
     * @var FinalizedMapperDefinition
     */
    protected $definition;

    /**
     * @var array
     */
    protected $validProperties;

    /**
     * @var IRelation[]
     */
    protected $relations;

    /**
     * @var MapperDefinition
     */
    protected $readDefinition;

    public function __construct(IOrm $orm)
    {
        $this->orm            = $orm;
        $this->readDefinition = new MapperDefinition($orm);
    }

    /**
     * @return IObjectMapper
     */
    public function getParentMapper()
    {
        return $this->mapper;
    }

    /**
     * @return FinalizedMapperDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Defines the read model type
     *
     * @param string $readModelType
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function type($readModelType)
    {
        if (!is_subclass_of($readModelType, IReadModel::class, true)) {
            throw InvalidArgumentException::format(
                    'Invalid class supplied to read model mapper definition: expecting instance of %s, %s given',
                    IReadModel::class, $readModelType
            );
        }

        $this->readDefinition->type($readModelType);
        /** @var string|IReadModel|TypedObject $readModelType */
        $this->class = $readModelType::definition();
    }

    /**
     * Defines the root entity mapper
     *
     * @param IObjectMapper $mapper
     *
     * @return void
     */
    public function from(IObjectMapper $mapper)
    {
        $this->mapper     = $mapper;
        $this->definition = $mapper->getDefinition();
        $this->relations  = $this->definition->getPropertyRelationMap();

        if ($mapper instanceof IEntityMapper) {
            $this->readDefinition->addColumn($mapper->getPrimaryTable()->getPrimaryKeyColumn());
        }

        $this->validProperties = $this->definition->getPropertyColumnMap() + $this->relations;
    }

    /**
     * Defines the root entity to load the read model data from.
     *
     * @param string      $entityClass
     * @param string|null $tableName
     *
     * @return void
     */
    public function fromType($entityClass, $tableName = null)
    {
        $this->from($this->orm->getEntityMapper($entityClass, $tableName));
    }

    /**
     * Maps the entity instance to the property
     * of the read model.
     *
     * @param string $propertyName
     *
     * @return void
     */
    public function entityTo($propertyName)
    {
        $this->readDefinition->relation($propertyName)
                ->asCustom(new EmbeddedObjectRelation(new EmbeddedMapperProxy($this->mapper)));
    }

    /**
     * Defines the properties of the entity to load.
     *
     * Example:
     * <code>
     * $map->properties(['foo' => 'bar']);
     * </code>
     *
     * Or if the property names are the same:
     * <code>
     * $map->properties(['foo']);
     * </code>
     *
     * @param string[]|callable[] $propertyAliasMap
     *
     * @return void
     */
    public function properties(array $propertyAliasMap)
    {
        $this->verifyClassDefined(__METHOD__);
        $this->verifyMapperDefined(__METHOD__);

        $propertyColumnMap = $this->definition->getPropertyColumnMap();
        $relations         = $this->definition->getPropertyRelationMap();
        $toPhpConverters   = $this->definition->getDbToPhpPropertyConverterMap();
        $table             = $this->definition->getTable();
        $emptyFunction     = function () {
        };

        foreach ($propertyAliasMap as $property => $alias) {
            if (is_int($property)) {
                $property = $alias;
            }

            $this->validatePropertyMapped($property);

            if (isset($propertyColumnMap[$property])) {
                $dbToPhpConverter = isset($toPhpConverters[$property])
                        ? $toPhpConverters[$property]
                        : function ($i) {
                            return $i;
                        };


                if (is_string($alias)) {
                    $columnDefiner = $this->readDefinition
                            ->property($alias)
                            ->mappedVia($emptyFunction, $dbToPhpConverter);
                } else {
                    $columnDefiner = $this->readDefinition
                            ->accessor($emptyFunction, $alias);
                }

                $columnDefiner
                        ->to($propertyColumnMap[$property])
                        ->asType($table->findColumn($propertyColumnMap[$property])->getType());
            } else {
                $relation = $relations[$property];

                if (is_string($alias)) {
                    $relationDefiner = $this->readDefinition->relation($alias);
                } else {
                    $relationDefiner = $this->readDefinition->accessorRelation($emptyFunction, $alias);
                }

                $relationDefiner->asCustom($relation);

                foreach ($relation->getParentColumnsToLoad() as $column) {
                    $this->readDefinition->addColumn($table->findColumn($column));
                }
            }
        }
    }

    /**
     * Defines columns to map to to the supplied properties
     *
     * @param string[] $columnsPropertyMap
     *
     * @throws InvalidArgumentException
     */
    public function columns(array $columnsPropertyMap)
    {
        $this->verifyClassDefined(__METHOD__);
        $this->verifyMapperDefined(__METHOD__);

        $table = $this->definition->getTable();

        foreach ($columnsPropertyMap as $column => $property) {
            if (!$table->hasColumn($column)) {
                throw InvalidArgumentException::format(
                        'Invalid column supplied to %s: \'%s\' does not exist on table \'%s\'',
                        __METHOD__, $column, $table->getName()
                );
            }

            $this->readDefinition
                    ->property($property)
                    ->to($column)
                    ->asType($table->findColumn($column)->getType());
        }
    }

    /**
     * Defines a relation to load.
     *
     * @param string $propertyName
     *
     * @return RelationAliasDefiner
     * @throws InvalidArgumentException
     */
    public function relation($propertyName)
    {
        $this->verifyClassDefined(__METHOD__);
        $this->verifyMapperDefined(__METHOD__);

        if (!isset($this->relations[$propertyName])) {
            throw InvalidArgumentException::format(
                    'Invalid property to load from parent %s: property must be mapped to a relation, \'%s\' given',
                    $this->mapper->getObjectType(), $propertyName
            );
        }

        /** @var IToOneRelation $relation */
        $relation   = $this->relations[$propertyName];
        $definition = new self($this->orm);
        $definition->from($relation->getMapper());

        return new RelationAliasDefiner($definition, function ($alias, callable $relationReferenceLoader) use ($relation) {
            if ($relation instanceof IToOneRelation) {
                $relation = $relation->withReference($relationReferenceLoader($relation));
            } elseif ($relation instanceof IToManyRelation) {
                $relation = $relation->withReference($relationReferenceLoader($relation));
            }

            if (is_string($alias)) {
                $relationDefiner = $this->readDefinition->relation($alias);
            } else {
                $relationDefiner = $this->readDefinition->accessorRelation(function () {}, $alias);
            }

            $relationDefiner->asCustom($relation);
        });
    }


    /**
     * Defines an embedded read model.
     *
     * @param GenericReadModelMapper $readModelMapper
     *
     * @return EmbeddedReadModelAliasDefiner
     */
    public function embedded(GenericReadModelMapper $readModelMapper)
    {
        $this->verifyClassDefined(__METHOD__);
        $this->verifyMapperDefined(__METHOD__);

        return new EmbeddedReadModelAliasDefiner(function ($alias) use ($readModelMapper) {
            $mapper = $readModelMapper->loadMapperFor($this->mapper);

            $this->readDefinition->embedded($alias)->using($mapper);
        });
    }

    /**
     * @return FinalizedMapperDefinition
     * @throws IncompleteMapperDefinitionException
     */
    public function finalize()
    {
        $this->verifyClassDefined(__METHOD__);
        $this->verifyMapperDefined(__METHOD__);

        return $this->readDefinition->finalize($this->definition->getTable()->getName());
    }

    private function validatePropertyMapped($property)
    {
        if (!isset($this->validProperties[$property])) {
            throw InvalidArgumentException::format(
                    'Invalid property supplied to read model definition: must be mapped to column or relation, \'%s\' given',
                    $property
            );
        }
    }

    private function verifyMapperDefined($method)
    {
        if (!$this->class) {
            throw InvalidOperationException::format(
                    'Invalid call to %s: mapper has not been defined, use $map->from(...)',
                    $method
            );
        }
    }

    private function verifyClassDefined($method)
    {
        if (!$this->class) {
            throw InvalidOperationException::format(
                    'Invalid call to %s: read model class has not been defined, use $map->type(...)',
                    $method
            );
        }
    }
}