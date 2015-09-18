<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Persistence\Db\Row;

/**
 * A mapping between a parent row and a child entity.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentChildItem
{
    /**
     * @var Row
     */
    private $parent;

    /**
     * @var mixed
     */
    private $child;

    public function __construct(Row $parent, $child)
    {
        $this->parent = $parent;
        $this->child  = $child;
    }

    /**
     * @return Row
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * @param mixed $child
     */
    public function setChild($child)
    {
        $this->child = $child;
    }
}