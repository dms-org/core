<?php

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\Mode\IdentifyingRelationMode;
use Dms\Core\Persistence\Db\Mapping\Relation\Mode\NonIdentifyingRelationMode;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationIdentityReference;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationObjectReference;
use Dms\Core\Persistence\Db\Mapping\Relation\ToOneRelation;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The to-one relation definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneRelationDefiner extends RelationTypeDefinerBase
{
    /**
     * @var bool
     */
    private $identifying = false;

    /**
     * @var string|null
     */
    private $bidirectionalRelationProperty;

    /**
     * Defines the relationship as an identifying relationship.
     *
     * This means that when related entities are removed from
     * the relationship, they are globally deleted.
     *
     * @return static
     */
    public function identifying()
    {
        $this->identifying = true;

        return $this;
    }

    /**
     * Defines the bidirectional relation property that is defined
     * on the related entity.
     *
     * The bidirectional relation for a one-to-one relation should
     * be one-to-one.
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
     * Sets the column name of the foreign key on the
     * related table.
     *
     * @param string $columnName
     *
     * @return void
     */
    public function withParentIdAs($columnName)
    {
        call_user_func($this->callback, function ($idString, Table $parentTable) use ($columnName) {
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

            return new ToOneRelation(
                    $idString,
                    $this->loadIds
                            ? new ToOneRelationIdentityReference($mapper)
                            : new ToOneRelationObjectReference($mapper, $this->bidirectionalRelationProperty),
                    $columnName,
                    $this->identifying
                            ? new IdentifyingRelationMode()
                            : new NonIdentifyingRelationMode()
            );
        });
    }
}