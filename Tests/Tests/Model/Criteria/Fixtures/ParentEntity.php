<?php

namespace Dms\Core\Tests\Model\Criteria\Fixtures;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentEntity extends Entity
{
    /**
     * @var string
     */
    public $data;

    /**
     * @var int
     */
    public $relatedId;

    /**
     * @var EntityIdCollection|int[]
     */
    public $relatedIds;

    /**
     * @var RelatedEntity
     */
    public $relatedEntity;

    /**
     * @var EntityCollection|RelatedEntity[]
     */
    public $relatedEntities;

    /**
     * ParentEntity constructor.
     *
     * @param string                           $data
     * @param int                              $relatedId
     * @param EntityIdCollection|\int[]        $relatedIds
     * @param RelatedEntity                    $relatedEntity
     * @param EntityCollection|RelatedEntity[] $relatedEntities
     */
    public function __construct($data, $relatedId = null, $relatedIds = [], RelatedEntity $relatedEntity = null, $relatedEntities = [])
    {
        parent::__construct();
        $this->data            = $data;
        $this->relatedId       = $relatedId;
        $this->relatedIds      = $relatedIds;
        $this->relatedEntity   = $relatedEntity;
        $this->relatedEntities = $relatedEntities;
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->data)->asString();

        $class->property($this->relatedId)->nullable()->asInt();

        $class->property($this->relatedIds)->asCollectionOf(Type::int());

        $class->property($this->relatedEntity)->nullable()->asObject(RelatedEntity::class);

        $class->property($this->relatedEntities)->asCollectionOf(RelatedEntity::type());
    }
}