<?php

namespace Iddigital\Cms\Core\Table\Data;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\IDataTable;
use Iddigital\Cms\Core\Table\ITableSection;
use Iddigital\Cms\Core\Table\ITableStructure;

/**
 * The table class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DataTable implements IDataTable
{
    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * @var ITableSection[]
     */
    protected $sections;

    /**
     * Table constructor.
     *
     * @param ITableStructure      $structure
     * @param ITableSection[]      $sections
     *
     * @throws InvalidArgumentException
     */
    public function __construct(ITableStructure $structure, array $sections)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'sections', $sections, ITableSection::class);
        $this->structure          = $structure;
        $this->sections           = $sections;

        foreach ($sections as $section) {
            if ($section->getStructure() !== $structure) {
                throw InvalidArgumentException::format(
                        'Invalid section supplied to %s: section table structure is not equal to the parent table structure',
                        __METHOD__
                );
            }
        }
    }

    /**
     * @return ITableStructure
     */
    final public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return ITableSection[]
     */
    final public function getSections()
    {
        return $this->sections;
    }
}