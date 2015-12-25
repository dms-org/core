<?php

namespace Dms\Core\Tests\Persistence\Db;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockDatabaseTestBase extends CmsTestCase
{
    /**
     * @var MockDatabase
     */
    protected $db;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->db = new MockDatabase();
    }

    /**
     * @return string
     */
    protected function getTableAndConstraintNamespace()
    {
        return '';
    }

    /**
     * @param array[] $expectedStructure
     *
     * @return void
     */
    protected function assertDatabaseStructureSameAs(array $expectedStructure)
    {
        $namespace = $this->getTableAndConstraintNamespace();
        $expected  = [];
        $actual    = [];

        foreach ($this->db->getTables() as $table) {
            $actual[$table->getName()] = array_values($table->getStructure()->getColumns());
        }

        foreach ($expectedStructure as $tableName => $columns) {
            $expected[$namespace . $tableName] = $columns;
        }

        ksort($expected);
        ksort($actual);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param array $tableRowSets
     *
     * @return void
     */
    protected function assertDatabaseDataSameAs(array $tableRowSets)
    {
        $namespace         = $this->getTableAndConstraintNamespace();
        $prefixedTableData = [];

        // Keys dont matter
        $dbData       = array_map('array_values', $this->db->getData());
        $tableRowSets = array_map('array_values', $tableRowSets);

        foreach ($tableRowSets as $tableName => $tableData) {
            $prefixedTableData[$namespace . $tableName] = $tableData;
        }

        // Order does not matter
        ksort($dbData);
        ksort($prefixedTableData);

        // Assert equals first for nicer error output
        $this->assertEquals($prefixedTableData, $dbData);
        $this->assertSame($prefixedTableData, $dbData);
    }

    /**
     * @param array $tableRowSetsMap
     *
     * @return void
     */
    public function setDataInDb(array $tableRowSetsMap)
    {
        $namespace         = $this->getTableAndConstraintNamespace();
        $prefixedTableData = [];

        foreach ($tableRowSetsMap as $tableName => $tableData) {
            $prefixedTableData[$namespace . $tableName] = $tableData;
        }

        $this->db->setData($prefixedTableData);
    }
}