<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\Accessor\PropertyAccessor;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\RelationMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\ToManyRelationMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation\ToOneRelationMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\IObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Locking\IOptimisticLockingStrategy;
use Iddigital\Cms\Core\Persistence\Db\Mapping\NullObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Util\Debug;

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
     * @param IOptimisticLockingStrategy[] $lockingStrategies
     * @param IPersistHook[]               $persistHooks
     * @param IObjectMapping[]             $subClassMappings
     * @param callable                     $relationMappingsFactory
     * @param callable                     $foreignKeysFactory
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
            array $lockingStrategies,
            array $persistHooks,
            array $subClassMappings,
            callable $relationMappingsFactory,
            callable $foreignKeysFactory
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
        $this->lockingStrategies           = $lockingStrategies;
        $this->persistHooks                = $persistHooks;

        foreach ($subClassMappings as $mapping) {
            $this->subClassMappings[$mapping->getObjectType()] = $mapping;
        }

        $this->relationMappingsFactory = $relationMappingsFactory;
        $this->foreignKeysFactory      = $foreignKeysFactory;
    }

    /**
     * @param IObjectMapper $parentMapper
     *
     * @throws InvalidArgumentException
     */
    public function initializeRelations(IObjectMapper $parentMapper)
    {
        if ($this->hasInitializedRelations) {
            return;
        }

        $relationMappings = call_user_func($this->relationMappingsFactory, $this->table, $parentMapper);

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

        foreach ($this->subClassMappings as $mapping) {
            $mapping->initializeRelations($parentMapper);
        }

        $this->hasInitializedRelations = true;
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
        $this->persistHooks[] = $persistHook;
    }

    /**
     * @return IOrm
     */
    public function getOrm()
    {
        return $this->orm;
    }

    /**
     * @return string
     */
    public function getClassName()
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
    public function withColumnsPrefixedBy($prefix)
    {
        if ($prefix === '') {
            return $this;
        }

        $table = $this->table->withPrefix($prefix);

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
                $foreignKeys[] = $foreignKey->withPrefix($prefix);
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
                $lockingStrategies,
                $persistHooks,
                $subClassMappings,
                $relationMappingsFactory,
                $foreignKeyFactory
        );

        if ($this->hasInitializedRelations) {
            $self->initializeRelations(new NullObjectMapper());
        }

        return $self;
    }

    /**
     * @return FinalizedClassDefinition
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string[]
     */
    public function getPropertyColumnMap()
    {
        return $this->propertyColumnNameMap;
    }

    /**
     * @return callable[]
     */
    public function getPhpToDbPropertyConverterMap()
    {
        return $this->phpToDbPropertyConverterMap;
    }

    /**
     * @return callable[]
     */
    public function getDbToPhpPropertyConverterMap()
    {
        return $this->dbToPhpPropertyConverterMap;
    }

    /**
     * @return callable[]
     */
    public function getColumnGetterMap()
    {
        return $this->columnGetterMap;
    }

    /**
     * @return callable[]
     */
    public function getColumnSetterMap()
    {
        return $this->columnSetterMap;
    }

    /**
     * @return string[]
     */
    public function getMethodColumnMap()
    {
        return $this->methodColumnNameMap;
    }

    /**
     * Gets the relations mapped to properties.
     *
     * @return IRelation[]
     */
    public function getPropertyRelationMap()
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
    public function getRelationMappings()
    {
        return array_merge($this->toOneRelations, $this->toManyRelations);
    }

    /**
     * @return ToOneRelationMapping[]
     */
    public function getToOneRelationMappings()
    {
        return $this->toOneRelations;
    }

    /**
     * @return ToManyRelationMapping[]
     */
    public function getToManyRelationMappings()
    {
        return $this->toManyRelations;
    }

    /**
     * @param string $property
     *
     * @return IRelation|null
     */
    public function getRelationMappedToProperty($property)
    {
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
    public function getRelationMappingsWith($dependencyMode)
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
    public function getLockingStrategies()
    {
        return $this->lockingStrategies;
    }

    /**
     * @return IPersistHook[]
     */
    public function getPersistHooks()
    {
        return $this->persistHooks;
    }

    /**
     * @return IObjectMapping[]
     */
    public function getSubClassMappings()
    {
        return $this->subClassMappings;
    }

    /**
     * @param string $dependencyMode
     *
     * @return IObjectMapping[]
     */
    public function getSubClassMappingsWith($dependencyMode)
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
    public function isForAbstractClass()
    {
        return $this->class->isAbstract();
    }

    /**
     * @param Table $table
     *
     * @return FinalizedMapperDefinition
     */
    public function withTable(Table $table)
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
    public function getPropertyLinkedToColumn($columnName)
    {
        $columnName = array_search($columnName, $this->propertyColumnNameMap, true);

        return $columnName ? $columnName : null;
    }
}