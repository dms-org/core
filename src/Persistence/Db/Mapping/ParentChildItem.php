<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Model\IEntity;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Persistence\Db\Row;

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
    public function getParent() : \Dms\Core\Persistence\Db\Row
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