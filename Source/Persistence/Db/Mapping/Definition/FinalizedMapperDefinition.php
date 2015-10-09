<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\IObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Locking\IOptimisticLockingStrategy;
use Iddigital\Cms\Core\Persistence\Db\Mapping\NullObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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
     * @var IToOneRelation[]
     */
    protected $toOneRelations = [];

    /**
     * @var IToManyRelation[]
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
     * @var callable
     */
    private $relationsFactory;

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
     * @param IObjectMapping[]             $subClassMappings
     * @param callable                     $relationsFactory
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
            array $subClassMappings,
            callable $relationsFactory,
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

        foreach ($subClassMappings as $mapping) {
            $this->subClassMappings[$mapping->getObjectType()] = $mapping;
        }

        $this->relationsFactory   = $relationsFactory;
        $this->foreignKeysFactory = $foreignKeysFactory;
    }

    /**
     * @param IObjectMapper $parentMapper
     *
     * @return void
     */
    public function initializeRelations(IObjectMapper $parentMapper)
    {
        if ($this->hasInitializedRelations) {
            return;
        }

        $relations = call_user_func($this->relationsFactory, $this->table, $parentMapper);

        foreach ($relations as $property => $relation) {
            if ($relation instanceof IToOneRelation) {
                $this->toOneRelations[$property] = $relation;
            } elseif ($relation instanceof IToManyRelation) {
                $this->toManyRelations[$property] = $relation;
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

        if ($this->hasInitializedRelations) {
            $relationsFactory = function () {
                return $this->getRelations();
            };

            $foreignKeyFactory = function () {
                return [];
            };
        } else {
            $relationsFactory  = $this->relationsFactory;
            $foreignKeyFactory = $this->foreignKeysFactory;
        }

        $relationsFactory = function (Table $parentTable, IObjectMapper $parentMapper) use ($relationsFactory, $prefix) {
            $relations = [];
            /** @var IRelation $relation */
            foreach ($relationsFactory($parentTable, $parentMapper) as $property => $relation) {
                $relations[$property] = $relation->withEmbeddedColumnsPrefixedBy($prefix);
            }

            return $relations;
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
                $subClassMappings,
                $relationsFactory,
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
     * @return IRelation[]
     */
    public function getRelations()
    {
        return $this->toOneRelations + $this->toManyRelations;
    }

    /**
     * @return IToOneRelation[]
     */
    public function getToOneRelations()
    {
        return $this->toOneRelations;
    }

    /**
     * @return IToManyRelation[]
     */
    public function getToManyRelations()
    {
        return $this->toManyRelations;
    }

    /**
     * @param string $property
     *
     * @return IRelation|null
     */
    public function getRelation($property)
    {
        $relations = $this->getRelations();

        return isset($relations[$property]) ? $relations[$property] : null;
    }

    /**
     * @param string $dependencyMode
     *
     * @return IRelation[]
     */
    public function getRelationsWith($dependencyMode)
    {
        $relations = [];

        foreach ($this->getRelations() as $property => $relation) {
            if ($relation->getDependencyMode() === $dependencyMode) {
                $relations[$property] = $relation;
            }
        }

        return $relations;
    }

    /**
     * @return IOptimisticLockingStrategy[]
     */
    public function getLockingStrategies()
    {
        return $this->lockingStrategies;
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
}