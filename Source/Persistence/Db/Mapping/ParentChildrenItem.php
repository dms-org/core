<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Persistence\Db\Row;

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