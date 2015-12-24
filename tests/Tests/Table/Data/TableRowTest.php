<?php

namespace Dms\Core\Tests\Table\Data;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Table\Data\TableRow;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableRowTest extends CmsTestCase
{
    public function testNewRow()
    {
        $row = new TableRow(['column' => ['component' => 'data']]);

        $this->assertSame(['column' => ['component' => 'data']], $row->getData());
        $this->assertSame(['component' => 'data'], $row->getCellData($this->mockColumn('column')));
        $this->assertSame(['component' => 'data'], $row->getCellData('column'));
        $this->assertSame('data', $row->getCellComponentData($this->mockColumn('column'), $this->mockComponent('component')));
        $this->assertSame('data', $row->getCellComponentData('column', 'component'));
        $this->assertSame('data', $row->getCellComponentData('column'));
        $this->assertSame(true, isset($row['column']));
        $this->assertSame(true, isset($row['column.component']));
        $this->assertSame('data', $row['column']);
        $this->assertSame('data', $row['column.component']);

        $this->assertSame(false, isset($row['non-existent']));
        $this->assertSame(false, isset($row['column.non-existent']));

        $this->assertThrows(function () use ($row) {
            $row->getCellData($this->mockColumn('non-existent'));
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($row) {
            $row['non-existent'];
        }, InvalidArgumentException::class);


        $this->assertThrows(function () use ($row) {
            $row['column.non-existent'];
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($row) {
            $row->getCellComponentData($this->mockColumn('column'), $this->mockComponent('non-existent'));
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($row) {
            $row->getCellComponentData($this->mockColumn('abc'), $this->mockComponent('component'));
        }, InvalidArgumentException::class);
    }

    public function testMultipleComponents()
    {
        $row = new TableRow(['column' => ['component' => 'data', 'other' => 'other data']]);

        $this->assertSame('data', $row->getCellComponentData('column', 'component'));
        $this->assertSame('other data', $row->getCellComponentData('column', 'other'));
        $this->assertSame(false, isset($row['column']));
        $this->assertSame(true, isset($row['column.component']));
        $this->assertSame(true, isset($row['column.other']));
        $this->assertSame('data', $row['column.component']);
        $this->assertSame('other data', $row['column.other']);

        $this->assertThrows(function () use ($row) {
            $row->getCellComponentData('column');
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($row) {
            $row['column'];
        }, InvalidArgumentException::class);
    }

    public function testMustOnlyContainArrays()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new TableRow(['column' => 'data']);
    }

    public function testUnimplementedMethods()
    {
        $row = new TableRow(['column' => ['component' => 'data']]);

        $this->assertThrows(function () use ($row) {
            unset($row['data']);
        }, NotImplementedException::class);

        $this->assertThrows(function () use ($row) {
            $row['data'] = 'foo';
        }, NotImplementedException::class);
    }

    /**
     * @param $name
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|IColumn
     */
    protected function mockColumn($name)
    {
        $mock = $this->getMockForAbstractClass(IColumn::class);
        $mock->method('getName')->willReturn($name);

        return $mock;
    }

    /**
     * @param $name
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|IColumnComponent
     */
    protected function mockComponent($name)
    {
        $mock = $this->getMockForAbstractClass(IColumnComponent::class);
        $mock->method('getName')->willReturn($name);

        return $mock;
    }
}