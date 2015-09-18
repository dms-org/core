<?php

namespace Iddigital\Cms\Core\Table\Data;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\IParameterizedAction;
use Iddigital\Cms\Core\Table\ITableRow;
use Iddigital\Cms\Core\Table\ITableSection;
use Iddigital\Cms\Core\Table\ITableStructure;

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
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @inheritDoc
     */
    public function hasGroupData()
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
    public function getRows()
    {
        return $this->rows;
    }
}