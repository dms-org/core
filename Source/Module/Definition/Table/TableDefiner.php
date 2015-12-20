<?php

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Model\IObjectSet;

/**
 * The table definer class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableDefiner extends TableDefinerBase
{
    /**
     * Sets the data source as an array of rows.
     *
     * @param array[] $rows
     *
     * @return ArrayTableDefiner
     */
    public function fromArray(array $rows)
    {
        return new ArrayTableDefiner($this->name, $this->callback, $rows);
    }

    /**
     * Sets the data source as a collection of objects.
     *
     * @param IObjectSet $objects
     *
     * @return ObjectTableDefiner
     */
    public function fromObjects(IObjectSet $objects)
    {
        return new ObjectTableDefiner($this->name, $this->callback, $objects);
    }
}