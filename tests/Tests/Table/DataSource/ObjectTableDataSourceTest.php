<?php

namespace Dms\Core\Tests\Table\DataSource;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\ObjectCollection;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\Data\Object\TableRowWithObject;
use Dms\Core\Table\DataSource\Definition\FinalizedObjectTableDefinition;
use Dms\Core\Table\DataSource\Definition\ObjectTableDefinition;
use Dms\Core\Table\DataSource\ObjectTableDataSource;
use Dms\Core\Table\ITableDataSource;
use Dms\Core\Table\ITableStructure;
use Dms\Core\Tests\Table\DataSource\Fixtures\TestPerson;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectTableDataSourceTest extends PeopleTableDataSourceTest
{
    /**
     * @var FinalizedObjectTableDefinition
     */
    protected $definition;

    /**
     * @var ObjectTableDataSource
     */
    protected $dataSource;

    /**
     * @return ITableStructure
     */
    protected function buildStructure()
    {
        $map = new ObjectTableDefinition(TestPerson::definition());

        $map->column(Column::name('name')->label('Name')->components([
            Field::name('first_name')->label('First Name')->string(),
            Field::name('last_name')->label('Last Name')->string(),
        ]));

        $map->property('firstName')->toComponent('name.first_name');
        $map->property('lastName')->toComponent('name.last_name');
        $map->property('age')->to(Field::name('age')->label('Age')->int());

        $this->definition = $map->finalize();

        return $this->definition->getStructure();
    }

    /**
     * @param ITableStructure $structure
     *
     * @return ITableDataSource
     */
    protected function buildDataSource(ITableStructure $structure)
    {
        return new ObjectTableDataSource($this->definition, new ObjectCollection(TestPerson::class, [
            new TestPerson('Joe', 'Go', 29),
            new TestPerson('Harold', 'Php', 38),
            new TestPerson('Samantha', 'Sharp', 20),
            new TestPerson('Joe', 'Java', 32),
            new TestPerson('Kelly', 'Rust', 18),
        ]));
    }

    public function testCanUseComponentInCriteria()
    {
        $this->assertSame(true, $this->dataSource->canUseColumnComponentInCriteria('name.first_name'));
        $this->assertSame(true, $this->dataSource->canUseColumnComponentInCriteria('name.last_name'));
        $this->assertSame(true, $this->dataSource->canUseColumnComponentInCriteria('age'));

        $this->assertThrows(function () {
            $this->dataSource->canUseColumnComponentInCriteria('non_existent');
        }, InvalidArgumentException::class);
    }

    public function testIncompatiblePropertyColumnMapping()
    {
        $this->expectException(InvalidArgumentException::class);

        $map = new ObjectTableDefinition(TestPerson::definition());
        $map->property('firstName')->to(Field::name('age')->label('Age')->int());

        $this->definition = $map->finalize();
        $this->buildDataSource($this->definition->getStructure());
    }

    public function testLoadFromObjects()
    {
        $dataTable = $this->dataSource->loadFromObjects([
            new TestPerson('A', '!!', 10),
            new TestPerson('B', '@@', 20),
        ]);

        $this->assertSameDataTable([
            [
                ['name' => ['first_name' => 'A', 'last_name' => '!!'], 'age' => ['age' => 10]],
                ['name' => ['first_name' => 'B', 'last_name' => '@@'], 'age' => ['age' => 20]],
            ],
        ], $dataTable);
    }

    public function testLoadsRowsWithAssociated()
    {
        $dataTable = $this->dataSource->load();

        $this->assertEquals([
            new TableRowWithObject(
                ['name' => ['first_name' => 'Joe', 'last_name' => 'Go'], 'age' => ['age' => 29]],
                new TestPerson('Joe', 'Go', 29)
            ),
            new TableRowWithObject(
                ['name' => ['first_name' => 'Harold', 'last_name' => 'Php'], 'age' => ['age' => 38]],
                new TestPerson('Harold', 'Php', 38)
            ),
            new TableRowWithObject(
                ['name' => ['first_name' => 'Samantha', 'last_name' => 'Sharp'], 'age' => ['age' => 20]],
                new TestPerson('Samantha', 'Sharp', 20)
            ),
            new TableRowWithObject(
                ['name' => ['first_name' => 'Joe', 'last_name' => 'Java'], 'age' => ['age' => 32]],
                new TestPerson('Joe', 'Java', 32)
            ),
            new TableRowWithObject(
                ['name' => ['first_name' => 'Kelly', 'last_name' => 'Rust'], 'age' => ['age' => 18]],
                new TestPerson('Kelly', 'Rust', 18)
            ),
        ], $dataTable->getSections()[0]->getRows());
    }
}