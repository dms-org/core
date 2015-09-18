<?php

namespace Iddigital\Cms\Core\Tests\Table\Data;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Module\IParameterizedAction;
use Iddigital\Cms\Core\Table\Data\TableSection;
use Iddigital\Cms\Core\Table\ITableRow;
use Iddigital\Cms\Core\Table\ITableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableSectionTest extends CmsTestCase
{
    public function testNewSectionWithoutGroup()
    {
        $structure = $this->getMockForAbstractClass(ITableStructure::class);
        $row       = $this->getMockForAbstractClass(ITableRow::class);
        $section   = new TableSection($structure, null, [$row, $row]);

        $this->assertSame($structure, $section->getStructure());
        $this->assertSame(null, $section->getGroupData());
        $this->assertSame(false, $section->hasGroupData());
        $this->assertSame([$row, $row], $section->getRows());
    }

    public function testInvalidRowsArray()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $structure = $this->getMockForAbstractClass(ITableStructure::class);
        new TableSection($structure, null, [1, 2]);
    }

    public function testNewWithGroupData()
    {
        $structure = $this->getMockForAbstractClass(ITableStructure::class);
        $groupData = $this->getMockForAbstractClass(ITableRow::class);
        $row       = $this->getMockForAbstractClass(ITableRow::class);
        $section   = new TableSection($structure, $groupData, [$row]);

        $this->assertSame($structure, $section->getStructure());
        $this->assertSame($groupData, $section->getGroupData());
        $this->assertSame(true, $section->hasGroupData());
        $this->assertSame([$row], $section->getRows());
    }
}