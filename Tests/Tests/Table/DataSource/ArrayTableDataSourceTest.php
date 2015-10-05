<?php

namespace Iddigital\Cms\Core\Tests\Table\DataSource;

use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Table\Builder\Table;
use Iddigital\Cms\Core\Table\DataSource\ArrayTableDataSource;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Table\ITableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayTableDataSourceTest extends PeopleTableDataSourceTest
{
    /**
     * @return ITableStructure
     */
    protected function buildStructure()
    {
        return Table::create([
                Column::name('name')->label('Name')->components([
                        Field::name('first_name')->label('First Name')->string(),
                        Field::name('last_name')->label('Last Name')->string(),
                ]),
                Column::from(Field::name('age')->label('Age')->int()),
        ]);
    }

    /**
     * @param string          $name
     * @param ITableStructure $structure
     *
     * @return ITableDataSource
     */
    protected function buildDataSource($name, ITableStructure $structure)
    {
        return new ArrayTableDataSource($name, $structure, [
                ['name' => ['first_name' => 'Joe', 'last_name' => 'Go'], 'age' => 29],
                ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => 38],
                ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => 20],
                ['name' => ['first_name' => 'Joe', 'last_name' => 'Java'], 'age' => 32],
                ['name' => ['first_name' => 'Kelly', 'last_name' => 'Rust'], 'age' => 18],
        ]);
    }
}