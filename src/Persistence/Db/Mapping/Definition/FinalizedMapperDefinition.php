<?php declare(strict_types=1);

namespace Dms\Core\Persistence\Db\Mapping\Definition;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor\PropertyAccessor;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\RelationMapping;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\ToManyRelationMapping;
use Dms\Core\Persistence\Db\Mapping\Definition\Relation\ToOneRelationMapping;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\IEmbeddedObjectMapping;
use Dms\Core\Persistence\Db\Mapping\Hierarchy\IObjectMapping;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\Locking\IOptimisticLockingStrategy;
use Dms\Core\Persistence\Db\Mapping\NullObjectMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Util\Debug;

/**
 * The finalized mapper definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedMapperDefinition extends MapperDefinitionBase
{
    /**
     * @var bool
     */
    private $hasInitializedRelations = false;

    /**
     * @var IOrm
     */
    private $orm;

    /**
     * @var Table
     */
    private $table;

    /**
     * @var Table
     */
    private $entityTable;

    /**
     * @var IObjectMapping[]
     */
    private $subClassMappings = [];

    /**
     * @var array
     */
    private $propertyColumnNameMap;

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
    protected $methodColumnNameMap = [];

    /**
     * @var ToOneRelationMapping[]
     */
    protected $toOneRelations = [];

    /**
     * @var ToManyRelationMapping[]
     */
    protected $toManyRelations = [];

    /**
     * @var array|\callable[]
     */
    private $phpToDbPropertyConverterMap;

    /**
     * @var array|\callable[]
     */
    private $dbToPhpPropertyConverterMap;

    /**
     * @var IOptimisticLockingStrategy[]
     */
    private $lockingStrategies = [];

    /**
     * @var IPersistHook[]
     */
    private $persistHooks = [];

    /**
     * @var callable
     */
    private $relationMappingsFactory;

    /**
     * @var callable
     */
    private $foreignKeysFactory;
    /**
     * @var array|\callable[]
     */
    private $customLoadMappingCallbacks;

    /**
     * FinalizedMapperDefinition constructor.
     *
     * @param IOrm                         $orm
     * @param FinalizedClassDefinition     $class
     * @param Table                        $table
     * @param string[]                     $propertyColumnNameMap
     * @param callable[]                   $columnGetterMap
     * @param callable[]                   $columnSetterMap
     * @param callable[]                   $phpToDbPropertyConverterMap
     * @param callable[]                   $dbToPhpPropertyConverterMap
     * @param string[]                     $methodColumnNameMap
     * @param callable[]                   $customLoadMappingCallbacks
     * @param IOptimisticLockingStrategy[] $lockingStrategies
     * @param IPersistHook[]               $persistHooks
     * @param IObjectMapping[]             $subClassMappings
     * @param callable                     $relationMappingsFactory
     * @param callable                     $foreignKeysFactory
     * @param Table|null                   $entityTable
     */
    public function __construct(
        IOrm $orm,
        FinalizedClassDefinition $class,
        Table $table,
        array $propertyColumnNameMap,
        array $columnGetterMap,
        array $columnSetterMap,
        array $phpToDbPropertyConverterMap,
        array $dbToPhpPropertyConverterMap,
        array $methodColumnNameMap,
        array $customLoadMappingCallbacks,
        array $lockingStrategies,
        array $persistHooks,
        array $subClassMappings,
        callable $relationMappingsFactory,
        callable $foreignKeysFactory,
        Table $entityTable = null
    ) {
        $this->orm                         = $orm;
        $this->class                       = $class;
        $this->table                       = $table;
        $this->propertyColumnNameMap       = $propertyColumnNameMap;
        $this->columnGetterMap             = $columnGetterMap;
        $this->columnSetterMap             = $columnSetterMap;
        $this->phpToDbPropertyConverterMap = $phpToDbPropertyConverterMap;
        $this->dbToPhpPropertyConverterMap = $dbToPhpPropertyConverterMap;
        $this->methodColumnNameMap         = $methodColumnNameMap;
        $this->customLoadMappingCallbacks  = $customLoadMappingCallbacks;
        $this->lockingStrategies           = $lockingStrategies;

        foreach ($persistHooks as $persistHook) {
            $this->persistHooks[$persistHook->getIdString()] = $persistHook;
        }

        foreach ($subClassMappings as $mapping) {
            $this->subClassMappings[$mapping->getObjectType()] = $mapping;
        }

        $this->orderSubclassMappingsBySpecificity();

        $this->relationMappingsFactory = $relationMappingsFactory;
        $this->foreignKeysFactory      = $foreignKeysFactory;
        $this->entityTable             = $entityTable ?: $table;
    }

    protected function orderSubclassMappingsBySpecificity()
    {
        /** @var IObjectMapping[][] $orderedSubClasses */
        $orderedSubClasses = [];

        foreach ($this->subClassMappings as $subclass => $mapping) {
            $orderedSubClasses[count(class_parents($subclass))][] = $mapping;
        }

        ksort($orderedSubClasses);

        $this->subClassMappings = [];
        foreach ($orderedSubClasses as $mappingGroup) {
            foreach ($mappingGroup as $mapping) {
                $this->subClassMappings[$mapping->getObjectType()] = $mapping;
            }
        }
    }

    /**
     * @param IObjectMapper $mapper
     *
     * @throws InvalidArgumentException
     */
    public function initializeRelations(IObjectMapper $mapper)
    {
        if ($this->hasInitializedRelations) {
            return;
        }

        if ($mapper instanceof IEmbeddedObjectMapper) {
            $this->entityTable = $mapper->getTableWhichThisIsEmbeddedWithin();
        } else {
            $this->entityTable = $this->table;
        }

        $relationMappings = call_user_func($this->relationMappingsFactory, $this->entityTable, $mapper);

        foreach ($relationMappings as $relationMapping) {
            if ($relationMapping instanceof ToOneRelationMapping) {
                $this->toOneRelations[] = $relationMapping;
            } elseif ($relationMapping instanceof ToManyRelationMapping) {
                $this->toManyRelations[] = $relationMapping;
            } else {
                throw InvalidArgumentException::format('Unknown relation mapping type: %s', Debug::getType($relationMapping));
            }
        }

        $this->table = $this->table->withForeignKeys(array_merge(
            $this->table->getForeignKeys(),
            call_user_func($this->foreignKeysFactory, $this->table)
        ));

        foreach ($this->subClassMappings as $subClassMapping) {
            $subClassMapping->initializeRelations($mapper);
        }

        $embeddedForeignKeys = [];

        foreach ($this->findEmbeddedObjectRelations($this->toOneRelations) as $relation) {
            foreach ($relation->getMapper()->getDefinition()->getTable()->getForeignKeys() as $foreignKey) {
                $embeddedForeignKeys[] = $foreignKey;
            }
        }

        foreach ($this->findEmbeddedSubclassMappings($this->subClassMappings) as $subClassMapping) {
            foreach ($subClassMapping->getDefinition()->getTable()->getForeignKeys() as $foreignKey) {
                $embeddedForeignKeys[] = $foreignKey;
            }
        }

        $this->table = $this->table->withForeignKeys(array_merge(
            $this->table->getForeignKeys(),
            $embeddedForeignKeys
        ));

        $this->hasInitializedRelations = true;
    }

    /**
     * @param RelationMapping[] $relations
     *
     * @return EmbeddedObjectRelation[]
     */
    protected function findEmbeddedObjectRelations(array $relations): array
    {
        $embeddedRelations = [];

        foreach ($relations as $relation) {
            if ($relation->getRelation() instanceof EmbeddedObjectRelation) {
                $embeddedRelations[] = $relation->getRelation();

                $embeddedRelations = array_merge(
                    $embeddedRelations,
                    $this->findEmbeddedObjectRelations($relation->getRelation()->getMapper()->getDefinition()->getToOneRelationMappings())
                );
            }
        }

        return $embeddedRelations;
    }

    /**
     * @param array $mappings
     *
     * @return IEmbeddedObjectMapping[]
     */
    protected function findEmbeddedSubclassMappings(array $mappings): array
    {
        $embeddedMappings = [];

        foreach ($mappings as $subClassMapping) {
            if ($subClassMapping instanceof IEmbeddedObjectMapping) {
                $embeddedMappings[] = $subClassMapping;

                $embeddedMappings = array_merge(
                    $embeddedMappings,
                    $this->findEmbeddedSubclassMappings($subClassMapping->getDefinition()->getSubClassMappings())
                );
            }
        }

        return $embeddedMappings;
    }


    /**
     * @param ForeignKey $foreignKey
     *
     * @return void
     */
    public function addForeignKey(ForeignKey $foreignKey)
    {
        $this->table = $this->table->withForeignKeys(
            array_merge($this->table->getForeignKeys(), [$foreignKey])
        );
    }

    /**
     * @param IPersistHook $persistHook
     *
     * @return void
     */
    public function addPersistHook(IPersistHook $persistHook)
    {
        $this->persistHooks[$persistHook->getIdString()] = $persistHook;
    }

    /**
     * @return IOrm
     */
    public function getOrm(): IOrm
    {
        return $this->orm;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->class->getClassName();
    }

    /**
     * Returns the equivalent definitions with the columns
     * prefixed by the supplied string.
     *
     * @param string $prefix
     *
     * @return FinalizedMapperDefinition
     */
    public function withColumnsPrefixedBy(string $prefix): FinalizedMapperDefinition
    {
        if ($prefix === '') {
            return $this;
        }

        $table       = $this->table->withColumnsPrefixedBy($prefix);
        $entityTable = $this->entityTable->withColumnsPrefixedBy($prefix);

        $propertyColumnNameMap = [];
        foreach ($this->propertyColumnNameMap as $property => $column) {
            $propertyColumnNameMap[$property] = $prefix . $column;
        }

        $columnGetterMap = [];
        foreach ($this->columnGetterMap as $column => $getter) {
            $columnGetterMap[$prefix . $column] = $getter;
        }

        $columnSetterMap = [];
        foreach ($this->columnSetterMap as $column => $setter) {
            $columnSetterMap[$prefix . $column] = $setter;
        }

        $methodColumnNameMap = [];
        foreach ($this->methodColumnNameMap as $property => $column) {
            $methodColumnNameMap[$property] = $prefix . $column;
        }

        $lockingStrategies = [];
        foreach ($this->lockingStrategies as $key => $lockingStrategy) {
            $lockingStrategies[$key] = $lockingStrategy->withColumnNamesPrefixedBy($prefix);
        }

        $persistHooks = [];
        foreach ($this->persistHooks as $key => $persistHook) {
            $persistHooks[$key] = $persistHook->withColumnNamesPrefixedBy($prefix);
        }

        if ($this->hasInitializedRelations) {
            $relationMappingsFactory = function () {
                return $this->getRelationMappings();
            };

            $foreignKeyFactory = function () {
                return [];
            };
        } else {
            $relationMappingsFactory = $this->relationMappingsFactory;
            $foreignKeyFactory       = $this->foreignKeysFactory;
        }

        $relationMappingsFactory = function (Table $parentTable, IObjectMapper $parentMapper) use ($relationMappingsFactory, $prefix) {
            $mappings = [];

            /** @var RelationMapping $mapping */
            foreach ($relationMappingsFactory($parentTable, $parentMapper) as $mapping) {
                $mappings[] = $mapping->withEmbeddedColumnsPrefixedBy($prefix);
            }

            return $mappings;
        };

        $foreignKeyFactory = function (Table $parentTable) use ($foreignKeyFactory, $prefix) {
            $foreignKeys = [];
            /** @var ForeignKey $foreignKey */
            foreach ($foreignKeyFactory($parentTable) as $foreignKey) {
                $foreignKeys[] = $foreignKey->withLocalColumnsPrefixedBy($prefix);
            }

            return $foreignKeys;
        };

        $subClassMappings = [];
        foreach ($this->subClassMappings as $mapping) {
            $subClassMappings[] = $mapping->withEmbeddedColumnsPrefixedBy($prefix);
        }

        $self = new self(
            $this->orm,
            $this->class,
            $table,
            $propertyColumnNameMap,
            $columnGetterMap,
            $columnSetterMap,
            $this->phpToDbPropertyConverterMap,
            $this->dbToPhpPropertyConverterMap,
            $methodColumnNameMap,
            $this->customLoadMappingCallbacks,
            $lockingStrategies,
            $persistHooks,
            $subClassMappings,
            $relationMappingsFactory,
            $foreignKeyFactory,
            $entityTable
        );

        if ($this->hasInitializedRelations) {
            $self->initializeRelations(new NullObjectMapper());
        }

        return $self;
    }

    /**
     * @return FinalizedClassDefinition
     */
    public function getClass(): FinalizedClassDefinition
    {
        return $this->class;
    }

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * Gets the table structure for the entity that contains this definition.
     *
     * @return Table
     */
    public function getEntityTable(): Table
    {
        return $this->entityTable;
    }

    /**
     * @return string[]
     */
    public function getPropertyColumnMap(): array
    {
        return $this->propertyColumnNameMap;
    }

    /**
     * @return callable[]
     */
    public function getPhpToDbPropertyConverterMap(): array
    {
        return $this->phpToDbPropertyConverterMap;
    }

    /**
     * @return callable[]
     */
    public function getDbToPhpPropertyConverterMap(): array
    {
        return $this->dbToPhpPropertyConverterMap;
    }

    /**
     * @return callable[]
     */
    public function getColumnGetterMap(): array
    {
        return $this->columnGetterMap;
    }

    /**
     * @return callable[]
     */
    public function getColumnSetterMap(): array
    {
        return $this->columnSetterMap;
    }

    /**
     * @return string[]
     */
    public function getMethodColumnMap(): array
    {
        return $this->methodColumnNameMap;
    }

    /**
     * @return callable[]
     */
    public function getCustomLoadMappingCallbacks(): array
    {
        return $this->customLoadMappingCallbacks;
    }

    /**
     * Gets the relations mapped to properties.
     *
     * @return IRelation[]
     */
    public function getPropertyRelationMap(): array
    {
        $relations = [];

        foreach ($this->getRelationMappings() as $mapping) {
            $accessor = $mapping->getAccessor();

            if ($accessor instanceof PropertyAccessor) {
                $relations[$accessor->getPropertyName()] = $mapping->getRelation();
            }
        }

        return $relations;
    }

    /**
     * @return RelationMapping[]
     */
    public function getRelationMappings(): array
    {
        return array_merge($this->toOneRelations, $this->toManyRelations);
    }

    /**
     * @return ToOneRelationMapping[]
     */
    public function getToOneRelationMappings(): array
    {
        return $this->toOneRelations;
    }

    /**
     * @return ToManyRelationMapping[]
     */
    public function getToManyRelationMappings(): array
    {
        return $this->toManyRelations;
    }

    /**
     * @return IRelation[]
     */
    public function getRelations(): array
    {
        $relations = [];

        foreach ($this->getRelationMappings() as $mapping) {
            $relations[] = $mapping->getRelation();
        }

        return $relations;
    }

    /**
     * @param string $property
     *
     * @return IRelation|null
     */
    public function getRelationMappedToProperty(string $property)
    {
        InvalidArgumentException::verify(is_string($property), 'property must be a string');
        $relations = $this->getRelationMappings();

        foreach ($relations as $mapping) {
            $accessor = $mapping->getAccessor();

            if ($accessor instanceof PropertyAccessor) {
                if ($accessor->getPropertyName() === $property) {

                    return $mapping->getRelation();
                }
            }
        }

        return null;
    }

    /**
     * @param string $dependencyMode
     *
     * @return RelationMapping[]
     */
    public function getRelationMappingsWith(string $dependencyMode): array
    {
        $mappings = [];

        foreach ($this->getRelationMappings() as $mapping) {
            if ($mapping->getRelation()->getDependencyMode() === $dependencyMode) {
                $mappings[] = $mapping;
            }
        }

        return $mappings;
    }

    /**
     * @return IOptimisticLockingStrategy[]
     */
    public function getLockingStrategies(): array
    {
        return $this->lockingStrategies;
    }

    /**
     * @return IPersistHook[]
     */
    public function getPersistHooks(): array
    {
        return $this->persistHooks;
    }

    /**
     * @param string $idString
     *
     * @return IPersistHook|null
     */
    public function getPersistHook(string $idString)
    {
        return isset($this->persistHooks[$idString]) ? $this->persistHooks[$idString] : null;
    }

    /**
     * @return IObjectMapping[]
     */
    public function getSubClassMappings(): array
    {
        return $this->subClassMappings;
    }

    /**
     * @param string $dependencyMode
     *
     * @return IObjectMapping[]
     */
    public function getSubClassMappingsWith(string $dependencyMode): array
    {
        $mappings = [];

        foreach ($this->getSubClassMappings() as $classType => $mapping) {
            if ($mapping->getDependencyMode() === $dependencyMode) {
                $mappings[$classType] = $mapping;
            }
        }

        return $mappings;
    }

    /**
     * @return bool
     */
    public function isForAbstractClass(): bool
    {
        return $this->class->isAbstract();
    }

    /**
     * @param Table $table
     *
     * @return FinalizedMapperDefinition
     */
    public function withTable(Table $table): FinalizedMapperDefinition
    {
        $clone        = clone $this;
        $clone->table = $table;

        return $clone;
    }

    /**
     * @param string $columnName
     *
     * @return string|null
     */
    public function getPropertyLinkedToColumn(string $columnName)
    {
        $columnName = array_search($columnName, $this->propertyColumnNameMap, true);

        return $columnName ? $columnName : null;
    }

    /**
     * @param string $class
     *
     * @return FinalizedMapperDefinition
     * @throws InvalidArgumentException
     */
    public function getSpecificSubclassMapping(string $class): self
    {
        if ($this->class->getClassName() === $class) {
            return $this;
        }

        if (!is_subclass_of($class, $this->class->getClassName(), true)) {
            throw InvalidArgumentException::format(
                'Invalid class supplied to %s: must be a subclass of %s, %s given',
                __METHOD__, $this->class->getClassName(), $class
            );
        }

        foreach ($this->subClassMappings as $mapping) {
            if (is_subclass_of($class, $mapping->getDefinition()->getClassName(), true)) {
                return $mapping->getDefinition()->getSpecificSubclassMapping($class);
            }
        }

        throw InvalidArgumentException::format(
            'Invalid class supplied to %s: cannot find mapping for %s',
            __METHOD__, $class
        );
    }

    /**
     * @param string $propertyName
     *
     * @return array|\string[]
     * @throws InvalidOperationException
     */
    public function getEmbeddedColumnsMappedTo(string $propertyName): array
    {
        $this->class->getProperty($propertyName);

        if (isset($this->propertyColumnNameMap[$propertyName])) {
            return [$this->propertyColumnNameMap[$propertyName]];
        }

        $relation = $this->getRelationMappedToProperty($propertyName);

        if ($relation) {
            return $relation->getParentColumnsToLoad();
        }

        throw InvalidOperationException::format('No columns mapped to property \'%s\'', $propertyName);
    }
}