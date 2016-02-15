<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

/**
 * The relation type definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelationDefiner extends RelationTypeDefinerBase
{
    /**
     * Defines the property as mapping as a to-one relationship
     * that will load the related entity object.
     *
     * @return ToOneRelationDefiner
     */
    public function toOne() : ToOneRelationDefiner
    {
        return new ToOneRelationDefiner($this->callback, $this->mapperLoader, $loadIds = false);
    }

    /**
     * Defines the property as mapping as a one-to-one relationship
     * that will load the related id.
     *
     * @return ToOneRelationDefiner
     */
    public function toOneId() : ToOneRelationDefiner
    {
        return new ToOneRelationDefiner($this->callback, $this->mapperLoader, $loadIds = true);
    }

    /**
     * Defines the property as mapping as a to-many relationship
     * that will load the related entities as a collection.
     *
     * @return ToManyRelationDefiner
     */
    public function toMany() : ToManyRelationDefiner
    {
        return new ToManyRelationDefiner($this->callback, $this->mapperLoader, $loadIds = false);
    }

    /**
     * Defines the property as mapping as a to-many relationship
     * that will load the related ids as a collection.
     *
     * @return ToManyRelationDefiner
     */
    public function toManyIds() : ToManyRelationDefiner
    {
        return new ToManyRelationDefiner($this->callback, $this->mapperLoader, $loadIds = true);
    }

    /**
     * Defines the property as mapping as a many-to-one relationship
     * that will load the related entity object.
     *
     * @return ManyToOneRelationDefiner
     */
    public function manyToOne() : ManyToOneRelationDefiner
    {
        return new ManyToOneRelationDefiner($this->callback, $this->mapperLoader, $loadIds = false);
    }

    /**
     * Defines the property as mapping as a many-to-one relationship
     * that will load the related id.
     *
     * @return ManyToOneRelationDefiner
     */
    public function manyToOneId() : ManyToOneRelationDefiner
    {
        return new ManyToOneRelationDefiner($this->callback, $this->mapperLoader, $loadIds = true);
    }
}