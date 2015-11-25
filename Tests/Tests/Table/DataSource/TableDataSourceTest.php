<?php

namespace Iddigital\Cms\Core\Tests\Table\DataSource;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\Builder\Table;
use Iddigital\Cms\Core\Table\Criteria\RowCriteria;
use Iddigital\Cms\Core\Table\IDataTable;
use Iddigital\Cms\Core\Table\IRowCriteria;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Table\ITableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TableDataSourceTest extends CmsTestCase
{
    /**
     * @var ITableStructure
     */
    protected $structure;

    /**
     * @var ITableDataSource
     */
    protected $dataSource;

    /**
     * @return ITableStructure
     */
    abstract protected function buildStructure();

    /**
     * @param ITableStructure $structure
     *
     * @return ITableDataSource
     */
    abstract protected function buildDataSource(ITableStructure $structure);

    public function setUp()
    {
        $this->structure  = $this->buildStructure();
        $this->dataSource = $this->buildDataSource($this->structure);
    }

    public function testStructure()
    {
        $this->assertSame($this->structure, $this->dataSource->getStructure());
    }

    public function testCriteria()
    {
        $criteria = $this->dataSource->criteria();

        $this->assertInstanceOf(RowCriteria::class, $criteria);
        $this->assertSame($this->structure, $criteria->getStructure());
    }

    public function testLoadInvalidCriteria()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $invalidCriteria = new RowCriteria(Table::create([]));
        $this->dataSource->load($invalidCriteria);
    }

    public function testCountInvalidCriteria()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $invalidCriteria = new RowCriteria(Table::create([]));
        $this->dataSource->count($invalidCriteria);
    }

    /**
     * @param array[]           $expectedSections
     * @param IRowCriteria|null $criteria
     *
     * @return void
     */
    protected function assertLoadsSections(array $expectedSections, IRowCriteria $criteria = null)
    {
        $table          = $this->dataSource->load($criteria);

        foreach ($table->getSections() as $section) {
            $this->assertSame($this->structure, $section->getStructure());
        }

        $actualSections = DataTableHelper::covertDataTableToNormalizedArray($table);

        $this->assertSame($this->structure, $table->getStructure());
        $this->assertEquals($expectedSections, $actualSections);
    }

    protected function assertLoadsCount($expectedCount, IRowCriteria $criteria = null)
    {
        $this->assertSame($expectedCount, $this->dataSource->count($criteria));
    }
}