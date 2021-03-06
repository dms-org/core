<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Persistence\Db\Row;

/**
 * A map class implementing a mapping from parent rows to a set of child entities.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParentChildrenMap extends ParentMapBase
{
    /**
     * @var ParentChildrenItem[]
     */
    private $items = [];

    /**
     * @return ParentChildrenItem[]
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
            foreach ($item->getChildren() as $child) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * @param Row   $parentRow
     * @param array $children
     *
     * @return void
     */
    public function add(Row $parentRow, array $children)
    {
        $this->items[] = new ParentChildrenItem($parentRow, $children);
    }
}