<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;

/**
 * The embedded object member mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedObjectMemberMapping extends MemberMapping
{
    /**
     * @var EmbeddedObjectRelation
     */
    protected $embeddedRelation;

    /**
     * EmbeddedObjectMemberMapping constructor.
     *
     * @param EmbeddedObjectRelation $embeddedRelation
     */
    public function __construct(EmbeddedObjectRelation $embeddedRelation)
    {
        $this->embeddedRelation = $embeddedRelation;
    }

    /**
     * @return EmbeddedObjectRelation
     */
    public function getEmbeddedRelation()
    {
        return $this->embeddedRelation;
    }

    /**
     * @return bool
     */
    public function isSingleColumnEmbeddedObject()
    {
        return count($this->embeddedRelation->getMapper()->getDefinition()->getTable()->getColumns()) === 1;
    }
}