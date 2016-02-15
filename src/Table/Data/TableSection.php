<?php declare(strict_types = 1);

namespace Dms\Core\Table\Data;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Table\ITableRow;
use Dms\Core\Table\ITableSection;
use Dms\Core\Table\ITableStructure;

/**
 * The table section class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableSection implements ITableSection
{
    /**
     * @var ITableStructure
     */
    private $structure;

    /**
     * @var ITableRow|null
     */
    private $groupData;

    /**
     * @var ITableRow[]
     */
    private $rows;

    /**
     * TableSection constructor.
     *
     * @param ITableStructure $structure
     * @param ITableRow|null  $groupData
     * @param ITableRow[]     $rows
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
            ITableStructure $structure,
            ITableRow $groupData = null,
            array $rows
    ) {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'rows', $rows, ITableRow::class);

        $this->structure = $structure;
        $this->groupData = $groupData;
        $this->rows      = $rows;
    }

    /**
     * @return ITableStructure
     */
    public function getStructure() : \Dms\Core\Table\ITableStructure
    {
        return $this->structure;
    }

    /**
     * @inheritDoc
     */
    public function hasGroupData() : bool
    {
        return $this->groupData !== null;
    }

    /**
     * @return ITableRow|null
     */
    public function getGroupData()
    {
        return $this->groupData;
    }

    /**
     * @return ITableRow[]
     */
    public function getRows() : array
    {
        return $this->rows;
    }
}