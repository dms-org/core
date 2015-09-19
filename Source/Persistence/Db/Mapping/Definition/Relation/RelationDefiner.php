<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

/**
 * The relation type definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelationDefiner extends RelationTypeDefinerBase
{
    /**
     * Defines the property as mapping as to to-one relationship
     * that will load the related entity object.
     *
     * @return ToOneRelationDefiner
     */
    public function toOne()
    {
        return new ToOneRelationDefiner($this->callback, $this->mapper, $loadIds = false);
    }

    /**
     * Defines the property as mapping as to one-to-one relationship
     * that will load the related id.
     *
     * @return ToOneRelationDefiner
     */
    public function toOneId()
    {
        return new ToOneRelationDefiner($this->callback, $this->mapper, $loadIds = true);
    }

    /**
     * Defines the property as mapping as to to-many relationship
     * that will load the related entities as a collection.
     *
     * @return ToManyRelationDefiner
     */
    public function toMany()
    {
        return new ToManyRelationDefiner($this->callback, $this->mapper, $loadIds = false);
    }

    /**
     * Defines the property as mapping as to to-many relationship
     * that will load the related ids as a collection.
     *
     * @return ToManyRelationDefiner
     */
    public function toManyIds()
    {
        return new ToManyRelationDefiner($this->callback, $this->mapper, $loadIds = true);
    }

    /**
     * Defines the property as mapping as to many-to-one relationship
     * that will load the related entity object.
     *
     * @return InverseToOneRelationDefiner
     */
    public function manyToOne()
    {
        return new InverseToOneRelationDefiner($this->callback, $this->mapper, $loadIds = false);
    }

    /**
     * Defines the property as mapping as to many-to-one relationship
     * that will load the related id.
     *
     * @return InverseToOneRelationDefiner
     */
    public function manyToOneId()
    {
        return new InverseToOneRelationDefiner($this->callback, $this->mapper, $loadIds = true);
    }
}