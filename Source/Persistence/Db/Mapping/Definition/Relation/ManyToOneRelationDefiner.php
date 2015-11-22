<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IdentifyingToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ManyToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\NonIdentifyingToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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
    private $onDeleteMode = ForeignKeyMode::SET_NULL;

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
    public function withBidirectionalRelation($propertyOnRelatedEntity)
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
    public function withRelatedIdAs($columnName)
    {
        call_user_func($this->callback,
                function ($idString, Table $parentTable) use ($columnName) {
                    $mapper = call_user_func($this->mapperLoader);

                    return new ManyToOneRelation(
                            $idString,
                            $this->loadIds
                                    ? new ToOneRelationIdentityReference($mapper)
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
                            ForeignKeyMode::CASCADE,
                            $this->onDeleteMode
                    );
                });
    }
}