<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Type\ObjectType;
use Dms\Core\Persistence\Db\Mapping\Definition\Column\ColumnTypeDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Column\GetterSetterColumnDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Column\PropertyColumnDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Embedded\EmbeddedCollectionDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Embedded\EmbeddedValueObjectDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Embedded\EnumPropertyColumnDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\ForeignKey\ForeignKeyLocalColumnsDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Hook\HookTypeDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Index\IndexColumnsDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor\CustomAccessor;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor\PropertyAccessor;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\IAccessor;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\RelationUsingDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\ToManyRelationMapping;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\ToOneRelationMapping;
use Dms\Core\Persistence\Db\Mapping\Definition\Subclass\SubClassMappingDefiner;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\Locking\IOptimisticLockingStrategy;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Util\Debug;

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
    protected $customLoadMappingCallbacks = [];

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

        if ($parent) {
            $this->verifyAllPropertiesMapped = $parent->verifyAllPropertiesMapped;
        }
    }

    /**
     * @return IOrm
     */
    public function getOrm() : IOrm
    {
        return $this->orm;
    }

    /**
     * Defines the default table name for the mapper.
     *
     * @param string $tableName
     *
     * @return void
     */
    public function toTable(string $tableName)
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
    public function type(string $classType)
    {
        $baseType = $this->class ? $this->class->getClassName() : TypedObject::class;
        if (!is_a($classType, $baseType, true)) {
            throw InvalidArgumentException::format(
                'Invalid call to %s: class type must be an subclass of %s, %s given',
                __METHOD__, $baseType, $classType
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
    public function idToPrimaryKey(string $columnName)
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
    public function primaryKey(string $columnName)
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
    public function column(string $columnName) : ColumnTypeDefiner
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
    public function property(string $propertyName) : PropertyColumnDefiner
    {
        $this->verifyProperty(__METHOD__, $propertyName);

        return new PropertyColumnDefiner($this, $propertyName, function (
            Column $column,
            callable $phpToDbPropertyConverter = null,
            callable $dbToPhpPropertyConverter = null,
            bool $ignoreNullabilityMismatch = false
        ) use ($propertyName) {
            $this->propertyColumnMap[$propertyName] = $column->getName();
            $this->addColumn($column);
            $this->mappedProperties[$propertyName] = true;

            $propertyType = $this->class->getProperty($propertyName)->getType();
            $columnType   = $column->getType()->getPhpType();

            if ($ignoreNullabilityMismatch) {
                $propertyType = $propertyType->nonNullable();
                $columnType   = $columnType->nonNullable();
            }

            if ($phpToDbPropertyConverter && $dbToPhpPropertyConverter) {
                $this->phpToDbPropertyConverterMap[$propertyName] = $phpToDbPropertyConverter;
                $this->dbToPhpPropertyConverterMap[$propertyName] = $dbToPhpPropertyConverter;
            } elseif (!$propertyType->equals($columnType)) {
                throw IncompatiblePropertyMappingException::format(
                    'Invalid property to column mapping: cannot bind %s to column \'%s\' as the types are incompatible, '
                    . 'property type %s must match the column type %s',
                    $this->class->getClassName() . '::$' . $propertyName, $column->getName(),
                    $propertyType->asTypeString(), $columnType->asTypeString()
                );
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
    public function accessor(callable $getter, callable $setter) : GetterSetterColumnDefiner
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
    public function method(string $methodName) : PropertyColumnDefiner
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
    public function computed(callable $computedPropertyCallback) : GetterSetterColumnDefiner
    {
        return new GetterSetterColumnDefiner($this, function (Column $column) use ($computedPropertyCallback) {
            $this->columnGetterMap[$column->getName()] = $computedPropertyCallback;
            $this->addColumn($column);
        });
    }

    /**
     * Defines a property containing an enum class.
     *
     * @see \Dms\Core\Model\Object\Enum
     *
     * @param string $property
     *
     * @return EnumPropertyColumnDefiner
     * @throws InvalidArgumentException
     */
    public function enum(string $property) : EnumPropertyColumnDefiner
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

        return $this->relation($property)->asEmbedded()->enum($class, $isNullable);
    }

    /**
     * Defines an embedded value object property.
     *
     * @param string $property
     *
     * @return EmbeddedValueObjectDefiner
     * @throws InvalidArgumentException
     */
    public function embedded(string $property) : EmbeddedValueObjectDefiner
    {
        $this->verifyProperty(__METHOD__, $property);

        return $this->relation($property)->asEmbedded()->object();
    }

    /**
     * Defines an embedded value object collection property.
     *
     * @param string $property
     *
     * @return EmbeddedCollectionDefiner
     * @throws InvalidArgumentException
     */
    public function embeddedCollection(string $property) : EmbeddedCollectionDefiner
    {
        $this->verifyProperty(__METHOD__, $property);

        return $this->relation($property)->asEmbedded()->collection();
    }

    /**
     * Defines a relationship property.
     *
     * @param string $property
     *
     * @return RelationUsingDefiner
     * @throws InvalidArgumentException
     */
    public function relation(string $property) : RelationUsingDefiner
    {
        $this->verifyProperty(__METHOD__, $property);

        $accessor = new PropertyAccessor($this->class->getClassName(), $this->class->getProperty($property));

        return $this->defineRelationVia($accessor, function () use ($property) {
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
    public function accessorRelation(callable $getter, callable $setter) : RelationUsingDefiner
    {
        return $this->defineRelationVia(new CustomAccessor($getter, $setter), function () {
            $this->verifyAllPropertiesMapped = false;
        });
    }

    /**
     * Defines a custom callback to load the supplied objects.
     *
     * Example:
     * <code>
     * $map->custom(function (array $objects) {
     *      foreach ($objects as $object) {
     *          // Load object...
     *      }
     * });
     * </code>
     *
     * This will be called after all relations and properties are loaded.
     *
     * @param callable $loadCallback
     *
     * @return void
     */
    public function custom(callable $loadCallback)
    {
        $this->customLoadMappingCallbacks[] = $loadCallback;
    }

    protected function defineRelationVia(IAccessor $accessor, callable $definedCallback = null)
    {
        return new RelationUsingDefiner(
            $this,
            $this->orm,
            $accessor,
            function (callable $relationFactory, callable $foreignKeyFactory = null) use ($accessor, $definedCallback) {
                $this->createRelationMappingFactory($accessor, $relationFactory);

                if ($foreignKeyFactory) {
                    $this->foreignKeyFactories[] = $foreignKeyFactory;
                }

                if ($definedCallback) {
                    $definedCallback();
                }
            }
        );
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
    public function subclass() : SubClassMappingDefiner
    {
        $this->verifyDefinedClass();

        return new SubClassMappingDefiner(
            $this->orm,
            $this,
            function (callable $mappingLoader, MapperDefinition $subClassDefinition) {
                $subClassDefinition->verifyDefinedClass();
                $subClassName = $subClassDefinition->class->getClassName();

                if (!is_a($subClassName, $this->class->getClassName(), true)) {
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
    public function index(string $indexName) : IndexColumnsDefiner
    {
        return new IndexColumnsDefiner(function (array $columnNames) use ($indexName) {
            $this->indexes[] = new Index($this->orm->getNamespace() . $indexName, false, $columnNames);
        });
    }

    /**
     * Defines a unique index on the supplied columns
     *
     * @param string $indexName
     *
     * @return IndexColumnsDefiner
     */
    public function unique(string $indexName) : IndexColumnsDefiner
    {
        return new IndexColumnsDefiner(function (array $columnNames) use ($indexName) {
            $this->indexes[] = new Index($this->orm->getNamespace() . $indexName, true, $columnNames);
        });
    }

    /**
     * Defines a foreign key.
     *
     * @param string $foreignKeyName
     *
     * @return ForeignKeyLocalColumnsDefiner
     */
    public function foreignKey(string $foreignKeyName) : ForeignKeyLocalColumnsDefiner
    {
        return new ForeignKeyLocalColumnsDefiner(function (
            array $localColumnNames,
            $referencedTable,
            array $referencedColumns,
            $onUpdateMode,
            $onDeleteMode
        ) use ($foreignKeyName) {
            $this->foreignKeys[] = new ForeignKey(
                $this->orm->getNamespace() . $foreignKeyName,
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
    public function hook() : Hook\HookTypeDefiner
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
    public function finalize(string $tableName = null) : FinalizedMapperDefinition
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

        $table = new Table(
            $this->orm->getNamespace() . $tableName,
            $this->columns,
            $this->indexes,
            $this->foreignKeys
        );

        $relationsFactory = function (Table $table, IObjectMapper $parentMapper) {
            $objectType = $parentMapper->getObjectType();
            $tableName  = $parentMapper->getDefinition()->getTable()->getName();

            $relations = [];
            foreach ($this->relationFactories as $uniqueKey => $factory) {
                $relationId  = implode(':', [$objectType, $tableName, $uniqueKey]);
                $relations[] = $factory($relationId, $table, $parentMapper);
            }

            return $relations;
        };

        $foreignKeysFactory = function (Table $table) {
            $foreignKeys = [];

            foreach ($this->foreignKeyFactories as $factory) {
                /** @var ForeignKey $foreignKey */
                $foreignKey    = $factory($table);
                $foreignKeys[] = $foreignKey->withNamePrefixedBy($this->orm->getNamespace());
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
            $this->customLoadMappingCallbacks,
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
    protected function buildPrimaryKeyColumn(string $name) : Column
    {
        return PrimaryKeyBuilder::incrementingInt($name);
    }

    /**
     * @return string|null
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return Column[]
     */
    public function getColumns() : array
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