<?php

namespace Dms\Core\Tests\Persistence\Db\Mock;

use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockForeignKey
{
    /**
     * @var MockTable
     */
    private $mainTable;

    /**
     * @var Column
     */
    private $mainColumn;

    /**
     * @var MockTable
     */
    private $referencedTable;

    /**
     * @var Column
     */
    private $referencedColumn;

    public function __construct(MockTable $mainTable, Column $mainColumn, MockTable $referencedTable, Column $referencedColumn)
    {
        $this->mainTable      = $mainTable;
        $this->mainColumn     = $mainColumn;
        $this->referencedTable  = $referencedTable;
        $this->referencedColumn = $referencedColumn;
    }

    /**
     * @return MockTable
     */
    public function getMainTable()
    {
        return $this->mainTable;
    }

    /**
     * @return Column
     */
    public function getMainColumn()
    {
        return $this->mainColumn;
    }

    /**
     * @return MockTable
     */
    public function getReferencedTable()
    {
        return $this->referencedTable;
    }

    /**
     * @return Column
     */
    public function getReferencedColumn()
    {
        return $this->referencedColumn;
    }

    public function validate()
    {
        $parent     = $this->mainTable->getColumnData($this->mainColumn->getName());
        $referenced = $this->referencedTable->getColumnData($this->referencedColumn->getName());

        foreach ($parent as $key => $parentId) {
            if ($parentId === null) {
                unset($parent[$key]);
            }
        }

        $invalidIds = array_diff($parent, $referenced);
        if ($invalidIds) {
            throw new ForeignKeyConstraintException($this, $invalidIds);
        }
    }
}