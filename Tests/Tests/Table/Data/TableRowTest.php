<?php

namespace Iddigital\Cms\Core\Tests\Table\Data;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\Data\TableRow;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;

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
        $this->assertSame('data', $row->getCellComponentData($this->mockColumn('column'), $this->mockComponent('component')));

        $this->assertThrows(function () use ($row) {
            $row->getCellData($this->mockColumn('non-existent'));
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($row) {
            $this->assertSame('data', $row->getCellComponentData($this->mockColumn('column'), $this->mockComponent('non-existent')));
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($row) {
            $this->assertSame('data', $row->getCellComponentData($this->mockColumn('abc'), $this->mockComponent('component')));
        }, InvalidArgumentException::class);
    }

    public function testMustOnlyContainArrays()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new TableRow(['column' => 'data']);
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