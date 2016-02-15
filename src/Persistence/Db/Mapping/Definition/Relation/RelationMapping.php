<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;

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
    public function getAccessor() : IAccessor
    {
        return $this->accessor;
    }

    /**
     * @return IRelation
     */
    public function getRelation() : \Dms\Core\Persistence\Db\Mapping\Relation\IRelation
    {
        return $this->relation;
    }

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withEmbeddedColumnsPrefixedBy(string $prefix)
    {
        $clone = clone $this;

        $clone->relation = $this->relation->withEmbeddedColumnsPrefixedBy($prefix);

        return $clone;
    }
}