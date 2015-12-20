<?php

namespace Dms\Core\Tests\Table\DataSource;

use Dms\Core\Table\IDataTable;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DataTableHelper
{
    /**
     * @param IDataTable $table
     *
     * @return array
     */
    public static function covertDataTableToNormalizedArray(IDataTable $table)
    {
        $actualSections = [];

        foreach ($table->getSections() as $section) {
            $arraySection = [];

            if ($section->hasGroupData()) {
                $arraySection['group_data'] = $section->getGroupData()->getData();
            }

            $rows = $section->getRows();
            foreach ($rows as $row) {
                $rowData        = $row->getData();
                ksort($rowData);
                $arraySection[] = $rowData;
            }

            $actualSections[] = $arraySection;
        }

        return $actualSections;
    }

    /**
     * @param array $sections
     *
     * @return array
     */
    public static function normalizeSingleComponents(array $sections)
    {
        foreach ($sections as $key => $section) {
            foreach ($section as $rowKey => $row) {
                ksort($sections[$key][$rowKey]);

                foreach ($row as $columnKey => $column) {
                    if (!is_array($column)) {
                        $sections[$key][$rowKey][$columnKey] = [$columnKey => $column];
                    }
                }
            }
        }

        return $sections;
    }
}