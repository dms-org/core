<?php

namespace Iddigital\Cms\Core\Tests\Table\DataSource;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\Builder\Table;
use Iddigital\Cms\Core\Table\Criteria\RowCriteria;
use Iddigital\Cms\Core\Table\IRowCriteria;
use Iddigital\Cms\Core\Table\ITableDataSource;
use Iddigital\Cms\Core\Table\ITableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TableDataSourceTest extends CmsTestCase
{
    /**
     * @var string
     */
    protected $name;

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
     * @return string
     */
    protected function buildName()
    {
        return 'table-data-source';
    }

    /**
     * @param string          $name
     * @param ITableStructure $structure
     *
     * @return ITableDataSource
     */
    abstract protected function buildDataSource($name, ITableStructure $structure);

    public function setUp()
    {
        $this->name       = $this->buildName();
        $this->structure  = $this->buildStructure();
        $this->dataSource = $this->buildDataSource($this->name, $this->structure);
    }

    public function testName()
    {
        $this->assertSame($this->name, $this->dataSource->getName());
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
        $actualSections = [];

        foreach ($table->getSections() as $section) {
            $this->assertSame($this->structure, $section->getStructure());

            $arraySection = [];

            if ($section->hasGroupData()) {
                $arraySection['group_data'] = $section->getGroupData()->getData();
            }

            $rows = $section->getRows();
            foreach ($rows as $row) {
                $arraySection[] = $row->getData();
            }

            $actualSections[] = $arraySection;
        }

        $this->assertSame($this->structure, $table->getStructure());
        $this->assertEquals($expectedSections, $actualSections);
    }

    protected function assertLoadsCount($expectedCount, IRowCriteria $criteria = null)
    {
        $this->assertSame($expectedCount, $this->dataSource->count($criteria));
    }
}