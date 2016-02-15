<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Persistence\Db\Row;

/**
 * A parent map base.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ParentMapBase
{
    /**
     * @var string
     */
    protected $primaryKeyColumn;

    /**
     * ParentMapBase constructor.
     *
     * @param string|null $primaryKeyColumn
     */
    public function __construct(string $primaryKeyColumn = null)
    {
        $this->primaryKeyColumn = $primaryKeyColumn;
    }

    /**
     * @return string
     */
    final public function getPrimaryKeyColumn() : string
    {
        return $this->primaryKeyColumn;
    }
    
    /**
     * @return int[]
     */
    public function getAllParentPrimaryKeys() : array
    {
        $keys = [];

        foreach ($this->getAllParents() as $parent) {
            $keys[] = $parent->getColumn($this->primaryKeyColumn);
        }

        return $keys;
    }

    /**
     * @return Row[]
     */
    abstract public function getAllParents() : array;

    /**
     * @return TypedObject[]
     */
    abstract public function getAllChildren() : array;

    /**
     * @return bool
     */
    final public function hasAnyParentsWithPrimaryKeys() : bool
    {
        foreach ($this->getAllParents() as $parent) {
            if ($parent->getColumn($this->primaryKeyColumn) !== null) {
                return true;
            }
        }

        return false;
    }
}