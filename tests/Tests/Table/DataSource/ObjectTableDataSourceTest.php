<?php

namespace Dms\Core\Tests\Table\DataSource;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\ObjectCollection;
use Dms\Core\Table\Builder\Column;
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
}