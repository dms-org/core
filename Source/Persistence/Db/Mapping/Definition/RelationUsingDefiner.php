<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;

/**
 * The relation using definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelationUsingDefiner
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * TypeTableDefiner constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Sets the relation to use the supplied mapper.
     *
     * @param IEntityMapper $mapper
     *
     * @return RelationDefiner
     */
    public function using(IEntityMapper $mapper)
    {
        return new RelationDefiner($this->callback, $mapper);
    }

    /**
     * Sets the relation to use the supplied relation instance.
     *
     * @param IRelation $relation
     *
     * @return void
     */
    public function asCustom(IRelation $relation)
    {
        call_user_func($this->callback, function () use ($relation) {
            return $relation;
        });
    }
}