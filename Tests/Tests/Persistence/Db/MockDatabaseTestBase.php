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
     * @param array[] $expectedStructure
     *
     * @return void
     */
    protected function assertDatabaseStructureSameAs(array $expectedStructure)
    {
        $actual = [];

        foreach ($this->db->getTables() as $table) {
            $actual[$table->getName()] = array_values($table->getStructure()->getColumns());
        }

        ksort($expectedStructure);
        ksort($actual);

        $this->assertEquals($expectedStructure, $actual);
    }

    /**
     * @param array $tableRowSets
     *
     * @return void
     */
    protected function assertDatabaseDataSameAs(array $tableRowSets)
    {
        // Keys dont matter
        $dbData = array_map('array_values', $this->db->getData());
        $tableRowSets = array_map('array_values', $tableRowSets);

        // Order does not matter
        ksort($dbData);
        ksort($tableRowSets);

        // Assert equals first nicer error output
        $this->assertEquals($tableRowSets, $dbData);
        $this->assertSame($tableRowSets, $dbData);
    }
}