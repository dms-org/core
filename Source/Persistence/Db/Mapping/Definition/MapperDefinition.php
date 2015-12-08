<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Type\ObjectType;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Column\ColumnTypeDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Column\GetterSetterColumnDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Column\PropertyColumnDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Embedded\EmbeddedCollectionDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Embedded\EmbeddedValueObjectDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Embedded\EnumPropertyColumnDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\ForeignKey\ForeignKeyLocalColumnsDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Hook\HookTypeDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Index\IndexColumnsDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor\CustomAccessor;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor\PropertyAccessor;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\IAccessor;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\RelationUsingDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\ToManyRelationMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\ToOneRelationMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Subclass\SubClassMappingDefiner;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EnumMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Locking\IOptimisticLockingStrategy;
use Iddigital\Cms\Core\Persistence\Db\Mapping\NullObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedCollectionRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\Index;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Boolean;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The mapper definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MapperDefinition extends MapperDefinitionBase
{
    /**
     * @var IOrm
     */
    private $orm;

    /**
     * @var MapperDefinition
     */
    private $parent;

    /**
     * @var string|null
     */
    protected $tableName;

    /**
     * @var Column[]
     */
    protected $columns = [];

    /**
     * @var Column
     */
    protected $primaryKey;

    /*
     * @var callable[]
     */
    protected $subClassMappingFactories = [];

    /**
     * @var string[]
     */
    protected $propertyColumnMap = [];

    /**
     * @var callable[]
     */
    protected $phpToDbPropertyConverterMap = [];

    /**
     * @var callable[]
     */
    protected $dbToPhpPropertyConverterMap = [];

    /**
     * @var callable[]
     */
    protected $columnSetterMap = [];

    /**
     * @var callable[]
     */
    protected $columnGetterMap = [];

    /**
     * @var string[]
     */
    protected $methodColumnMap = [];

    /**
     * @var callable[]
     */
    protected $relationFactories = [];

    /**
     * @var callable[]
     */
    protected $foreignKeyFactories = [];

    /**
     * @var Index[]
     */
    protected $indexes = [];

    /**
     * @var ForeignKey[]
     */
    protected $foreignKeys = [];

    /**
     * @var array
     */
    private $mappedProperties = [];

    /**
     * @var bool
     */
    private $verifyAllPropertiesMapped = true;

    /**
     * @var IOptimisticLockingStrategy[]
     */
    private $lockingStrategies = [];

    /**
     * @var callable[]
     */
    private $persistHookFactories = [];

    /**
     * MapperDefinition constructor.
     *
     * @param IOrm                  $orm
     * @param MapperDefinition|null $parent
     */
    public function __construct(IOrm $orm, MapperDefinition $parent = null)
    {
        $this->orm    = $orm;
        $this->parent = $parent;
    }

    /**
     * Defines the default table name for the mapper.
     *
     * @param string $tableName
     *
     * @return void
     */
    public function toTable($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Defines the type of mapped class.
     *
     * @param string $classType
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function type($classType)
    {
        if (!is_subclass_of($classType, TypedObject::class, true)) {
            throw InvalidArgumentException::format(
                    'Cannot map type %s: must be an subclass of %s',
                    $classType, TypedObject::class
            );
        }

        /** @var TypedObject $classType */
        $this->class = $classType::definition();
    }

    /**
     * Defines the primary key column name.
     *
     * @param string $columnName
     *
     * @return void
     */
    public function idToPrimaryKey($columnName)
    {
        $this->verifyProperty(__METHOD__, IEntity::ID);
        $this->propertyColumnMap[IEntity::ID] = $columnName;
        $this->mappedProperties[IEntity::ID]  = true;
        $this->primaryKey($columnName);
    }

    /**
     * Defines an unmapped primary key column.
     *
     * @param string $columnName
     *
     * @return void
     */
    public function primaryKey($columnName)
    {
        $this->primaryKey = $this->buildPrimaryKeyColumn($columnName);
        $this->columns    = [$columnName => $this->primaryKey] + $this->columns;
    }

    /**
     * Defines a column on the mapped table.
     *
     * This column is not mapped to properties in this mapper
     * but can be used to implement relations via foreign keys.
     *
     * @param string $columnName
     *
     * @return ColumnTypeDefiner
     */
    public function column($columnName)
    {
        return new ColumnTypeDefiner(
                $this,
                function (Column $column) {
                    $this->addColumn($column);
                },
                $columnName
        );
    }

    /**
     * Defines a column on the mapped table.
     *
     * @param Column $column
     *
     * @return void
     */
    public function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    private function verifyProperty($method, $property)
    {
        $this->verifyDefinedClass();

        if (!$this->class->hasProperty($property)) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: property \'%s\' is not a defined property on %s',
                    $method, $property, $this->class->getClassName()
            );
        }
    }

    /**
     * Defines a mapping between the class property to a the table column
     *
     * @param string $propertyName
     *
     * @return PropertyColumnDefiner
     * @throws InvalidArgumentException
     */
    public function property($propertyName)
    {
        $this->verifyProperty(__METHOD__, $propertyName);

        return new PropertyColumnDefiner($this, $propertyName, function (
                Column $column,
                callable $phpToDbPropertyConverter = null,
                callable $dbToPhpPropertyConverter = null
        ) use ($propertyName) {
            $this->propertyColumnMap[$propertyName] = $column->getName();
            $this->addColumn($column);
            $this->mappedProperties[$propertyName] = true;

            if ($phpToDbPropertyConverter && $dbToPhpPropertyConverter) {
                $this->phpToDbPropertyConverterMap[$propertyName] = $phpToDbPropertyConverter;
                $this->dbToPhpPropertyConverterMap[$propertyName] = $dbToPhpPropertyConverter;
            }
        });
    }

    /**
     * Defines the mapper definition to ignore all the
     * unmapped properties instead of throwing an exception.
     *
     * @return void
     */
    public function ignoreUnmappedProperties()
    {
        $this->verifyAllPropertiesMapped = false;
    }

    /**
     * Defines a mapping between a getter and setter callback and a column.
     *
     * Example:
     * <code>
     * ->accessor(
     *      function (SomeEntity $entity) {
     *          return $entity->getData();
     *      },
     *      function (SomeEntity $entity, $data) {
     *          $entity->setData($data);
     *      }
     * )
     * ->to('column_name')->asText();
     * </code>
     *
     * @param callable $getter
     * @param callable $setter
     *
     * @return GetterSetterColumnDefiner
     */
    public function accessor(callable $getter, callable $setter)
    {
        return new GetterSetterColumnDefiner($this, function (Column $column) use ($getter, $setter) {
            $this->columnGetterMap[$column->getName()] = $getter;
            $this->columnSetterMap[$column->getName()] = $setter;
            $this->addColumn($column);

            $this->verifyAllPropertiesMapped = false;
        });
    }

    /**
     * Defines a mapping between a method call to a column
     *
     * @param string $methodName
     *
     * @return PropertyColumnDefiner
     */
    public function method($methodName)
    {
        return new PropertyColumnDefiner($this, null, function (Column $column) use ($methodName) {
            $this->methodColumnMap[$methodName] = $column->getName();
            $this->addColumn($column);
        });
    }

    /**
     * Defines a mapping between the results of a computed property
     * to a column.
     *
     * Example:
     * <code>
     * ->computed(function ($entity) {
     *      return $entity->getData();
     * })
     * ->to('column_name')->asInt();
     * </code>
     *
     * @param callable $computedPropertyCallback
     *
     * @return GetterSetterColumnDefiner
     */
    public function computed(callable $computedPropertyCallback)
    {
        return new GetterSetterColumnDefiner($this, function (Column $column) use ($computedPropertyCallback) {
            $this->columnGetterMap[$column->getName()] = $computedPropertyCallback;
            $this->addColumn($column);
        });
    }

    /**
     * Defines a property containing an enum class.
     *
     * @see \Iddigital\Cms\Core\Model\Object\Enum
     *
     * @param string $property
     *
     * @return EnumPropertyColumnDefiner
     * @throws InvalidArgumentException
     */
    public function enum($property)
    {
        $this->verifyProperty(__METHOD__, $property);
        $type       = $this->class->getPropertyType($property);
        $isNullable = $type->isNullable();
        $enumType   = $type->nonNullable();

        if (!($enumType instanceof ObjectType)) {
            throw InvalidArgumentException::format(
                    'Invalid enum property \'%s\': must map to an object type, %s given',
                    $property, $type->asTypeString()
            );
        }

        $class = $enumType->getClass();

        return new EnumPropertyColumnDefiner(function ($columnName, array $valueMap = null) use ($property, $class, $isNullable) {
            $enumMapper = new EnumMapper($this->orm, $isNullable, $columnName, $class, $valueMap);

            $this->createRelationMappingFactory(new PropertyAccessor($property), function ($idString) use ($enumMapper) {
                return new EmbeddedObjectRelation($idString, $enumMapper, $enumMapper->getEnumValueColumn()->getName());
            });

            $this->addColumn($enumMapper->getEnumValueColumn());
            $this->mappedProperties[$property] = true;
        });
    }

    /**
     * Defines an embedded value object property.
     *
     * @param string $property
     *
     * @return EmbeddedValueObjectDefiner
     * @throws InvalidArgumentException
     */
    public function embedded($property)
    {
        $this->verifyProperty(__METHOD__, $property);

        return new EmbeddedValueObjectDefiner($this->orm, function (callable $mapperLoader, $issetColumnName = null) use ($property) {

            $this->createRelationMappingFactory(new PropertyAccessor($property),
                    function ($idString, Table $parentTable, IObjectMapper $parentMapper) use (
                            $mapperLoader,
                            $issetColumnName
                    ) {
                        return new EmbeddedObjectRelation($idString, $mapperLoader($parentMapper), $issetColumnName);
                    });

            $this->mappedProperties[$property] = true;

            if ($issetColumnName) {
                $this->addColumn(new Column($issetColumnName, new Boolean()));
                $isNullable = $issetColumnName !== null;
            } else {
                $isNullable = false;
            }

            // Use null object mapper as parent to load the columns
            /** @var IEmbeddedObjectMapper $tempMapper */
            $tempMapper = $mapperLoader(new NullObjectMapper());
            foreach ($tempMapper->getDefinition()->getTable()->getColumns() as $column) {
                $this->addColumn($isNullable ? $column->asNullable() : $column);
            }
        });
    }

    /**
     * Defines an embedded value object collection property.
     *
     * @param string $property
     *
     * @return EmbeddedCollectionDefiner
     * @throws InvalidArgumentException
     */
    public function embeddedCollection($property)
    {
        $this->verifyProperty(__METHOD__, $property);

        return new EmbeddedCollectionDefiner($this->orm,
                function (callable $mapperLoader, $tableName, $primaryKeyName, $foreignKeyName) use (
                        $property
                ) {
                    $this->createRelationMappingFactory(new PropertyAccessor($property),
                            function ($idString, Table $parentTable, IObjectMapper $parentMapper) use (
                                    $mapperLoader,
                                    $tableName,
                                    $primaryKeyName,
                                    $foreignKeyName
                            ) {
                                return new EmbeddedCollectionRelation(
                                        $idString,
                                        $mapperLoader($parentMapper),
                                        $parentTable->getName(),
                                        $tableName,
                                        $this->buildPrimaryKeyColumn($primaryKeyName),
                                        new Column($foreignKeyName, Integer::normal()),
                                        $this->primaryKey
                                );
                            });

                    $this->mappedProperties[$property] = true;
                });
    }

    /**
     * Defines a relationship property.
     *
     * @param string $property
     *
     * @return RelationUsingDefiner
     * @throws InvalidArgumentException
     */
    public function relation($property)
    {
        $this->verifyProperty(__METHOD__, $property);

        return $this->defineRelationVia(new PropertyAccessor($property), function () use ($property) {
            $this->mappedProperties[$property] = true;
        });
    }

    /**
     * Defines a relationship via a custom accessor.
     *
     * @param callable $getter
     * @param callable $setter
     *
     * @return RelationUsingDefiner
     * @throws InvalidArgumentException
     */
    public function accessorRelation(callable $getter, callable $setter)
    {
        return $this->defineRelationVia(new CustomAccessor($getter, $setter), function () {
            $this->verifyAllPropertiesMapped = false;
        });
    }

    protected function defineRelationVia(IAccessor $accessor, callable $definedCallback = null)
    {
        return new RelationUsingDefiner($this->orm,
                function (callable $relationFactory, callable $foreignKeyFactory = null) use ($accessor, $definedCallback) {
                    $this->createRelationMappingFactory($accessor, $relationFactory);

                    if ($foreignKeyFactory) {
                        $this->foreignKeyFactories[] = $foreignKeyFactory;
                    }

                    if ($definedCallback) {
                        $definedCallback();
                    }
                });
    }

    protected function createRelationMappingFactory(IAccessor $accessor, callable $relationFactory)
    {
        $this->relationFactories[] = function ($idString, Table $table, IObjectMapper $parentMapper) use ($accessor, $relationFactory) {
            $relation = $relationFactory($idString, $table, $parentMapper);

            if ($relation instanceof IToOneRelation) {
                return new ToOneRelationMapping($accessor, $relation);
            } elseif ($relation instanceof IToManyRelation) {
                return new ToManyRelationMapping($accessor, $relation);
            }

            throw InvalidArgumentException::format('Unknown relation type: %s', Debug::getType($relation));
        };
    }

    /**
     * Defines a mapper for properties of a subclass of parent object.
     *
     * @return SubClassMappingDefiner
     * @throws IncompleteMapperDefinitionException
     */
    public function subclass()
    {
        $this->verifyDefinedClass();

        return new SubClassMappingDefiner(
                $this->orm,
                $this,
                function (callable $mappingLoader, MapperDefinition $subClassDefinition) {
                    $subClassDefinition->verifyDefinedClass();
                    $subClassName = $subClassDefinition->class->getClassName();

                    if (!is_subclass_of($subClassName, $this->class->getClassName(), true)) {
                        throw IncompleteMapperDefinitionException::format(
                                'Invalid subclass mapper definition for: defined type must be a subclass of %s, %s given',
                                $this->class->getClassName(),
                                $subClassName
                        );
                    }

                    $this->subClassMappingFactories[$subClassName] = $mappingLoader;
                }
        );
    }

    /**
     * Defines an index on the supplied column names
     *
     * @param string $indexName
     *
     * @return IndexColumnsDefiner
     */
    public function index($indexName)
    {
        return new IndexColumnsDefiner(function (array $columnNames) use ($indexName) {
            $this->indexes[] = new Index($indexName, false, $columnNames);
        });
    }

    /**
     * Defines a unique index on the supplied columns
     *
     * @param string $indexName
     *
     * @return IndexColumnsDefiner
     */
    public function unique($indexName)
    {
        return new IndexColumnsDefiner(function (array $columnNames) use ($indexName) {
            $this->indexes[] = new Index($indexName, true, $columnNames);
        });
    }

    /**
     * Defines a foreign key.
     *
     * @param string $foreignKeyName
     *
     * @return ForeignKeyLocalColumnsDefiner
     */
    public function foreignKey($foreignKeyName)
    {
        return new ForeignKeyLocalColumnsDefiner(function (
                array $localColumnNames,
                $referencedTable,
                array $referencedColumns,
                $onUpdateMode,
                $onDeleteMode
        ) use ($foreignKeyName) {
            $this->foreignKeys[] = new ForeignKey(
                    $foreignKeyName,
                    $localColumnNames,
                    $referencedTable,
                    $referencedColumns,
                    $onUpdateMode,
                    $onDeleteMode
            );
        });
    }

    /**
     * Adds an optimistic locking strategy to use during persistence.
     *
     * @param IOptimisticLockingStrategy $strategy
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function optimisticLocking(IOptimisticLockingStrategy $strategy)
    {
        foreach ($strategy->getLockingColumnNames() as $columnName) {
            if (!isset($this->columns[$columnName])) {
                throw InvalidArgumentException::format(
                        'Cannot add optimistic locking strategy: expecting one of columns (%s), \'%s\' given',
                        Debug::formatValues(array_keys($this->columns)), $columnName
                );
            }
        }

        $this->lockingStrategies[] = $strategy;
    }

    /**
     * Defines a persist hook.
     *
     * @return HookTypeDefiner
     */
    public function hook()
    {
        return new HookTypeDefiner(function (callable $persistHookFactory) {
            $this->persistHookFactories[] = $persistHookFactory;
        });
    }

    protected function getAllMappedProperties()
    {
        return ($this->parent ? $this->parent->getAllMappedProperties() : []) + $this->mappedProperties;
    }

    /**
     * @param string|null $tableName
     *
     * @return FinalizedMapperDefinition
     * @throws IncompleteMapperDefinitionException
     */
    public function finalize($tableName = null)
    {
        $this->verifyDefinedClass();

        $tableName = $tableName ?: $this->tableName;

        $allMappedProperties = $this->getAllMappedProperties();

        foreach ($this->class->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($this->verifyAllPropertiesMapped && !isset($allMappedProperties[$propertyName])) {
                throw IncompleteMapperDefinitionException::format(
                        'Invalid mapper definition for %s: unmapped property \'%s\' of type %s, call $map->ignoreUnmappedProperties() to ignore this warning',
                        $this->class->getClassName(),
                        $propertyName,
                        $property->getType()->asTypeString()
                );
            }
        }

        $table = new Table($tableName, $this->columns, $this->indexes, $this->foreignKeys);

        $relationsFactory = function (Table $table, IObjectMapper $parentMapper) {
            $objectType  = $parentMapper->getObjectType();
            $tableName   = $parentMapper->getDefinition()->getTable()->getName();

            $relations = [];
            foreach ($this->relationFactories as $uniqueKey => $factory) {
                $relationId  = implode(':', [$objectType, $tableName, $uniqueKey]);
                $relations[] = $factory($relationId,  $table, $parentMapper);
            }

            return $relations;
        };

        $foreignKeysFactory = function (Table $table) {
            $foreignKeys = [];

            foreach ($this->foreignKeyFactories as $factory) {
                $foreignKeys[] = $factory($table);
            }

            return $foreignKeys;
        };

        $persistHooks = [];
        foreach ($this->persistHookFactories as $uniqueKey => $factory) {
            $persistHooks[] = $factory($table, $uniqueKey, $this->propertyColumnMap, $this->class->getClassName());
        }

        $subClassMappings = [];
        foreach ($this->subClassMappingFactories as $factory) {
            $subClassMappings[] = $factory($table);
        }

        return new FinalizedMapperDefinition(
                $this->orm,
                $this->class,
                $table,
                $this->propertyColumnMap,
                $this->columnGetterMap,
                $this->columnSetterMap,
                $this->phpToDbPropertyConverterMap,
                $this->dbToPhpPropertyConverterMap,
                $this->methodColumnMap,
                $this->lockingStrategies,
                $persistHooks,
                $subClassMappings,
                $relationsFactory,
                $foreignKeysFactory
        );
    }

    /**
     * @return void
     * @throws IncompleteMapperDefinitionException
     */
    protected function verifyDefinedClass()
    {
        if (!$this->class) {
            throw IncompleteMapperDefinitionException::format(
                    'Invalid mapper definition: mapped object type is not defined, use $map->type(...)'
            );
        }
    }

    /**
     * @param string $name
     *
     * @return Column
     */
    protected function buildPrimaryKeyColumn($name)
    {
        return new Column($name, Integer::normal()->autoIncrement(), true);
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Column[] $columns
     *
     * @return void
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }
}