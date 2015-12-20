<?php

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Model\IEntity;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Persistence\Db\Row;

/**
 * A mapping between a parent row and children entities.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentChildrenItem
{
    /**
     * @var Row
     */
    private $parent;

    /**
     * @var array
     */
    private $children = [];

    public function __construct(Row $parent, array $children)
    {
        $this->parent   = $parent;
        $this->children = $children;
    }

    /**
     * @return Row
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param array $children
     *
     * @return void
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }
}