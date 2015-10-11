<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;

/**
 * The relation mapping base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationMapping
{
    /**
     * @var IAccessor
     */
    protected $accessor;

    /**
     * @var IRelation
     */
    protected $relation;

    /**
     * RelationMapping constructor.
     *
     * @param IAccessor $accessor
     * @param IRelation $relation
     */
    public function __construct(IAccessor $accessor, IRelation $relation)
    {
        $this->accessor = $accessor;
        $this->relation = $relation;
    }

    /**
     * @return IAccessor
     */
    public function getAccessor()
    {
        return $this->accessor;
    }

    /**
     * @return IRelation
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withEmbeddedColumnsPrefixedBy($prefix)
    {
        $clone = clone $this;

        $clone->relation = $this->relation->withEmbeddedColumnsPrefixedBy($prefix);

        return $clone;
    }
}