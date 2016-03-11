<?php declare(strict_types = 1);

namespace Dms\Core\Table\DataSource\Definition;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Table\ITableStructure;

/**
 * The finalized grouped table data source definition.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedGroupedTableDefinition
{
    /**
     * @var ITableDataSource
     */
    protected $dataSource;

    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * @var string[]
     */
    protected $groupByComponentIds;

    /**
     * @var callable[]
     */
    protected $componentIdCallableMap;

    /**
     * FinalizedGroupedTableDataSourceDefinition constructor.
     *
     * @param ITableDataSource          $dataSource
     * @param ITableStructure           $structure
     * @param \string[]                 $groupByComponentIds
     * @param \callable[]               $componentIdCallableMap
     */
    public function __construct(ITableDataSource $dataSource, ITableStructure $structure, array $groupByComponentIds, array $componentIdCallableMap)
    {
        InvalidArgumentException::verify(!empty($groupByComponentIds), 'Group by components cannot be empty');

        $this->dataSource             = $dataSource;
        $this->structure              = $structure;
        $this->groupByComponentIds    = $groupByComponentIds;
        $this->componentIdCallableMap = $componentIdCallableMap;
    }

    /**
     * @return ITableDataSource
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @return ITableStructure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return string[]
     */
    public function getGroupByComponentIds()
    {
        return $this->groupByComponentIds;
    }

    /**
     * @return callable[]
     */
    public function getComponentIdCallableMap()
    {
        return $this->componentIdCallableMap;
    }
}