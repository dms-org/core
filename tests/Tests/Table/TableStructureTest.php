<?php

namespace Dms\Core\Tests\Table;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\Builder\Column;
use Dms\Core\Table\TableStructure;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableStructureTest extends CmsTestCase
{
    public function testNewStructure()
    {
        $structure = new TableStructure([
                $string = Column::from(Field::name('string')->label('String')->string()),
                $number = Column::from(Field::name('number')->label('Number')->decimal()),
        ]);

        $this->assertSame(['string' => $string, 'number' => $number], $structure->getColumns());
        $this->assertSame(true, $structure->hasColumn('string'));
        $this->assertSame(false, $structure->hasColumn('abc'));
        $this->assertSame($string, $structure->getColumn('string'));
        $this->assertSame($number, $structure->getColumn('number'));
        $this->assertSame(['string', 'number'], $structure->getColumnNames());

        $this->assertSame([$string, $string->getComponent()], $structure->getColumnAndComponent('string'));
        $this->assertSame([$string, $string->getComponent('string')], $structure->getColumnAndComponent('string.string'));

        $this->assertSame($string->getComponent(), $structure->getComponent('string'));
        $this->assertSame($string->getComponent('string'), $structure->getComponent('string.string'));

        $this->assertThrows(function () use ($structure) {
            $structure->getColumn('other-column');
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($structure) {
            $structure->getColumnAndComponent('string.invalid');
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($structure) {
            $structure->getColumnAndComponent('invalid');
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($structure) {
            $structure->getComponent('invalid');
        }, InvalidArgumentException::class);
    }

    public function testWithColumns()
    {
        $structure = new TableStructure([
                $string = Column::from(Field::name('string')->label('String')->string()),
                $number = Column::from(Field::name('number')->label('Number')->decimal()),
        ]);

        $structure = $structure->withColumns([$number]);

        $this->assertSame(['number' => $number], $structure->getColumns());
        $this->assertSame(true, $structure->hasColumn('number'));
        $this->assertSame(false, $structure->hasColumn('string'));
        $this->assertSame(['number'], $structure->getColumnNames());
    }
}