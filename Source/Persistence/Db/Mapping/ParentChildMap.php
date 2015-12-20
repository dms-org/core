<?php

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Persistence\Db\Row;

/**
 * A map class implementing a mapping from parent rows to child entities.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentChildMap extends ParentMapBase
{
    /**
     * @var ParentChildItem[]
     */
    private $items = [];

    /**
     * @return ParentChildItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return Row[]
     */
    public function getAllParents()
    {
        $rows = [];

        foreach ($this->items as $item) {
            $rows[] = $item->getParent();
        }

        return $rows;
    }

    /**
     * @return array
     */
    public function getAllChildren()
    {
        $children = [];

        foreach ($this->items as $item) {
            $children[] = $item->getChild();
        }

        return $children;
    }

    /**
     * @param Row   $parentRow
     * @param mixed $child
     *
     * @return void
     */
    public function add(Row $parentRow, $child)
    {
        $this->items[] = new ParentChildItem($parentRow, $child);
    }
}