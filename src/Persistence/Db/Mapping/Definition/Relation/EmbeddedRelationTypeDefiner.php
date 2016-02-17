<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\Definition\Embedded\EmbeddedCollectionDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Embedded\EmbeddedValueObjectDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\Embedded\EnumPropertyColumnDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EnumMapper;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\NullObjectMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedCollectionRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Boolean;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use InvalidArgumentException;

/**
 * The embedded relation type definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedRelationTypeDefiner
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
     * EmbeddedRelationTypeDefiner constructor.
     *
     * @param MapperDefinition $definition
     * @param IOrm             $orm
     * @param IAccessor        $accessor
     * @param callable         $callback
     */
    public function __construct(MapperDefinition $definition, IOrm $orm, IAccessor $accessor, callable $callback)
    {
        $this->definition = $definition;

        $this->orm      = $orm;
        $this->accessor = $accessor;
        $this->callback = $callback;
    }

    /**
     * Defines a relation mapped to an enum class.
     *
     * @see \Dms\Core\Model\Object\Enum
     *
     * @param string $class
     * @param bool $isNullable
     *
     * @return EnumPropertyColumnDefiner
     * @throws InvalidArgumentException
     */
    public function enum(string $class, bool $isNullable = false) : EnumPropertyColumnDefiner
    {
        return new EnumPropertyColumnDefiner(function ($columnName, array $valueMap = null) use ($class, $isNullable) {
            $enumMapper = new EnumMapper($this->orm, $isNullable, $columnName, $class, $valueMap);
            $this->definition->addColumn($enumMapper->getEnumValueColumn());

            call_user_func($this->callback, function ($idString) use ($enumMapper) {
                return new EmbeddedObjectRelation($idString, $enumMapper, $enumMapper->getEnumValueColumn()->getName());
            });
        });
    }

    /**
     * Defines an embedded value object relation.
     *
     * @return EmbeddedValueObjectDefiner
     * @throws InvalidArgumentException
     */
    public function object() : EmbeddedValueObjectDefiner
    {
        return new EmbeddedValueObjectDefiner($this->orm, function (callable $mapperLoader, $issetColumnName = null) {
            if ($issetColumnName) {
                $this->definition->addColumn(new Column($issetColumnName, new Boolean()));
                $isNullable = $issetColumnName !== null;
            } else {
                $isNullable = false;
            }

            // Use null object mapper as parent to load the columns
            /** @var IEmbeddedObjectMapper $tempMapper */
            $tempMapper = $mapperLoader(new NullObjectMapper());
            foreach ($tempMapper->getDefinition()->getTable()->getColumns() as $column) {
                $this->definition->addColumn($isNullable ? $column->asNullable() : $column);
            }

            call_user_func($this->callback, function ($idString, Table $parentTable, IObjectMapper $parentMapper) use (
                    $mapperLoader,
                    $issetColumnName
            ) {
                return new EmbeddedObjectRelation($idString, $mapperLoader($parentMapper), $issetColumnName);
            });
        });
    }

    /**
     * Defines an embedded value object collection property.
     *
     * @return EmbeddedCollectionDefiner
     * @throws InvalidArgumentException
     */
    public function collection() : EmbeddedCollectionDefiner
    {
        return new EmbeddedCollectionDefiner(
                $this->orm,
                function (callable $mapperLoader, $tableName, $primaryKeyName, $foreignKeyName) {
                    call_user_func($this->callback, function ($idString, Table $parentTable, IObjectMapper $parentMapper) use (
                            $mapperLoader,
                            $tableName,
                            $primaryKeyName,
                            $foreignKeyName
                    ) {
                        return new EmbeddedCollectionRelation(
                                $idString,
                                $mapperLoader($parentMapper),
                                $parentTable->getName(),
                                $this->orm->getNamespace() . $tableName,
                                $parentTable->getPrimaryKeyColumn()->withName($primaryKeyName),
                                new Column($foreignKeyName, PrimaryKeyBuilder::primaryKeyType()),
                                $parentTable->getPrimaryKeyColumn()
                        );
                    });
                }
        );
    }
}