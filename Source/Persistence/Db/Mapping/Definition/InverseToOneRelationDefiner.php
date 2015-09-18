<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IdentifyingToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ManyToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\NonIdentifyingToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The inverse to-one relation definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InverseToOneRelationDefiner extends RelationTypeDefinerBase
{
    /**
     * @var string|null
     */
    private $bidirectionalRelationProperty;

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
     * Sets the column name of the foreign key on the parent table.
     *
     * @param string $columnName
     *
     * @return void
     */
    public function withRelatedIdAs($columnName)
    {
        call_user_func($this->callback, function (Table $parentTable) use ($columnName) {
            return new ManyToOneRelation(
                    $this->loadIds
                            ? new ToOneRelationIdentityReference($this->mapper)
                            : new ToOneRelationObjectReference($this->mapper, $this->bidirectionalRelationProperty),
                    $parentTable,
                    $columnName
            );
        });
    }
}