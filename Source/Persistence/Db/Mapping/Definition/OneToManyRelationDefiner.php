<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IdentifyingRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\NonIdentifyingRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationIdentityReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToManyRelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ToManyRelation;

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

    public function __construct(callable $callback, IEntityMapper $mapper, $loadIds, $identifying)
    {
        parent::__construct($callback, $mapper, $loadIds);
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
     * Defines the relationship as one-to-many mapping the parent
     * id to the supplied column.
     *
     * @param string $column
     *
     * @return void
     */
    public function withParentIdAs($column)
    {
        call_user_func($this->callback, function () use ($column) {
            return new ToManyRelation(
                    $this->loadIds
                            ? new ToManyRelationIdentityReference($this->mapper)
                            : new ToManyRelationObjectReference($this->mapper, $this->bidirectionalRelationProperty),
                    $column,
                    $this->identifying
                            ? new IdentifyingRelationMode()
                            : new NonIdentifyingRelationMode()
            );
        });
    }
}