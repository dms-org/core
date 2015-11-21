<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Persistence\Db\Row;

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
     * @param string $primaryKeyColumn
     */
    public function __construct($primaryKeyColumn)
    {
        $this->primaryKeyColumn = $primaryKeyColumn;
    }

    /**
     * @return string
     */
    final public function getPrimaryKeyColumn()
    {
        return $this->primaryKeyColumn;
    }
    
    /**
     * @return int[]
     */
    public function getAllParentPrimaryKeys()
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
    abstract public function getAllParents();

    /**
     * @return TypedObject[]
     */
    abstract public function getAllChildren();

    /**
     * @return bool
     */
    final public function hasAnyParentsWithPrimaryKeys()
    {
        foreach ($this->getAllParents() as $parent) {
            if ($parent->getColumn($this->primaryKeyColumn) !== null) {
                return true;
            }
        }

        return false;
    }
}