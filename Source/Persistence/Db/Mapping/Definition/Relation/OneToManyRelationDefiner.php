<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IdentifyingRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\NonIdentifyingRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Query\Clause\Ordering;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The one-to-many relation definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneToManyRelationDefiner extends RelationTypeDefinerBase
{
    /**
     * @var string|null
     */
    protected $bidirectionalRelationProperty;

    /**
     * @var bool
     */
    private $identifying;

    /**
     * @var string[]
     */
    protected $orderByColumnNameDirectionMap = [];

    /**
     * @var string|null
     */
    protected $orderPersistColumn;

    public function __construct(callable $callback, callable $mapperLoader, $loadIds, $identifying)
    {
        parent::__construct($callback, $mapperLoader, $loadIds);
        $this->identifying = $identifying;
    }

    /**
     * Defines the bidirectional relation property that is defined
     * on the related entity.
     *
     * For a many-to-many relation the bidirectional relation should
     * also be many-to-many.
     *
     * For a one-to-many relation the bidirectional relation should be
     * many-to-one.
     *
     * NOTE: This is only used for object relations. Not id relations.
     *
     * @param string $propertyOnRelatedEntity
     *
     * @return static
     */
    public function withBidirectionalRelation($propertyOnRelatedEntity)
    {
        $this->bidirectionalRelationProperty = $propertyOnRelatedEntity;

        return $this;
    }

    /**
     * Defines the one-to-many relation to load the collection
     * ordered by the supplied column ascendingly.
     *
     * @param string $columnName
     *
     * @return OneToManyRelationDefiner
     */
    public function orderByAsc($columnName)
    {
        $this->orderByColumnNameDirectionMap[$columnName] = Ordering::ASC;

        return $this;
    }

    /**
     * Defines the one-to-many relation to load the collection
     * ordered by the supplied column descendingly.
     *
     * @param string $columnName
     *
     * @return OneToManyRelationDefiner
     */
    public function orderByDesc($columnName)
    {
        $this->orderByColumnNameDirectionMap[$columnName] = Ordering::DESC;

        return $this;
    }

    /**
     * Defines the one-to-many relation to persist the 1-based
     * order index of the related objects to the supplied column.
     * This will automatically load the columns in the persisted order.
     *
     * @param string $columnName
     *
     * @return OneToManyRelationDefiner
     */
    public function withOrderPersistedTo($columnName)
    {
        $this->orderPersistColumn = $columnName;
        $this->orderByAsc($columnName);

        return $this;
    }

    /**
     * Defines the relationship as one-to-many mapping the parent
     * id to the supplied column.
     *
     * @param string $columnName
     *
     * @return void
     */
    public function withParentIdAs($columnName)
    {
        call_user_func($this->callback, function (Table $parentTable) use ($columnName) {
            /** @var IEntityMapper $mapper */
            $mapper = call_user_func($this->mapperLoader);
            $mapper->addForeignKey(ForeignKey::createWithNamingConvention(
                    $mapper->getPrimaryTableName(),
                    [$columnName],
                    $parentTable->getName(),
                    [$parentTable->getPrimaryKeyColumnName()],
                    ForeignKeyMode::CASCADE,
                    $this->identifying
                            ? ForeignKeyMode::CASCADE
                            : ForeignKeyMode::SET_NULL
            ));

            return new ToManyRelation(
                    $this->loadIds
                            ? new ToManyRelationIdentityReference($mapper)
                            : new ToManyRelationObjectReference($mapper, $this->bidirectionalRelationProperty),
                    $columnName,
                    $this->identifying
                            ? new IdentifyingRelationMode()
                            : new NonIdentifyingRelationMode(),
                    $this->orderByColumnNameDirectionMap,
                    $this->orderPersistColumn
            );
        });
    }
}