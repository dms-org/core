<?php declare(strict_types = 1);

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
    public function getItems() : array
    {
        return $this->items;
    }

    /**
     * @return Row[]
     */
    public function getAllParents() : array
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
    public function getAllChildren() : array
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