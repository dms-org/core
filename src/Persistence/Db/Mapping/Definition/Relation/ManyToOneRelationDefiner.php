<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\ManyToOneRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationIdentityReference;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationObjectReference;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The many-to-one relation definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ManyToOneRelationDefiner extends RelationTypeDefinerBase
{
    /**
     * @var string|null
     */
    private $bidirectionalRelationProperty;

    /**
     * @var string
     */
    private $onDeleteMode = ForeignKeyMode::DO_NOTHING;

    /**
     * Defines the bidirectional relation property that is defined
     * on the related entity.
     *
     * The bidirectional relation for a many-to-one relation should
     * be one-to-many.
     *
     * @param string $propertyOnRelatedEntity
     *
     * @return static
     */
    public function withBidirectionalRelation(string $propertyOnRelatedEntity)
    {
        $this->bidirectionalRelationProperty = $propertyOnRelatedEntity;

        return $this;
    }

    /**
     * Defines the foreign key to delete the objects when the related objects
     * are deleted.
     *
     * @return static
     */
    public function onDeleteCascade()
    {
        return $this->onDelete(ForeignKeyMode::CASCADE);
    }

    /**
     * Defines the foreign key to set the columns to null
     * when the related objects are deleted.
     *
     * @return static
     */
    public function onDeleteSetNull()
    {
        return $this->onDelete(ForeignKeyMode::SET_NULL);
    }

    /**
     * Defines the foreign key to throw an error when the related objects
     * are deleted.
     *
     * @return static
     */
    public function onDeleteDoNothing()
    {
        return $this->onDelete(ForeignKeyMode::DO_NOTHING);
    }

    private function onDelete($mode)
    {
        if ($this->bidirectionalRelationProperty) {
            throw InvalidOperationException::format(
                    'Cannot set foreign key delete mode on many-to-one relation: bidirectional relation property is set ' .
                    'so the foreign key will be defined on the inverse one-to-many relation'
            );
        }

        $this->onDeleteMode = $mode;

        return $this;
    }

    /**
     * Sets the column name of the foreign key on the parent table.
     *
     * @param string $columnName
     *
     * @return void
     */
    public function withRelatedIdAs(string $columnName)
    {
        call_user_func($this->callback,
                function ($idString, Table $parentTable) use ($columnName) {
                    $mapper = call_user_func($this->mapperLoader);

                    return new ManyToOneRelation(
                            $idString,
                            $this->loadIds
                                    ? new ToOneRelationIdentityReference($mapper, $this->bidirectionalRelationProperty)
                                    : new ToOneRelationObjectReference($mapper, $this->bidirectionalRelationProperty),
                            $parentTable,
                            $columnName
                    );
                },
                // Dont create foreign key if bidirectional property is set
                // because it will be created by the inverse bidirectional relation
                /** @see OneToManyRelationDefiner::withParentIdAs() */
                /** @see ToOneRelationDefiner::withParentIdAs() */
                $this->bidirectionalRelationProperty ? null : function (Table $parentTable) use ($columnName) {
                    /** @var IEntityMapper $mapper */
                    $mapper = call_user_func($this->mapperLoader);

                    return ForeignKey::createWithNamingConvention(
                            $parentTable->getName(),
                            [$columnName],
                            $mapper->getPrimaryTableName(),
                            [$mapper->getPrimaryTable()->getPrimaryKeyColumnName()],
                            $this->onDeleteMode,
                            ForeignKeyMode::CASCADE
                    );
                });
    }
}