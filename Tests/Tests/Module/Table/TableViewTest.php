<?php

namespace Iddigital\Cms\Core\Tests\Module\Table;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Module\Table\TableView;
use Iddigital\Cms\Core\Table\IRowCriteria;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableViewTest extends CmsTestCase
{
    public function testWithCriteria()
    {
        $criteria = $this->getMockForAbstractClass(IRowCriteria::class);

        $criteria->method('asNewCriteria')
                ->willReturn(clone $criteria);

        $view = new TableView('name', 'Label', false, $criteria);

        $this->assertSame('name', $view->getName());
        $this->assertSame('Label', $view->getLabel());
        $this->assertSame(false, $view->isDefault());
        $this->assertSame(true, $view->hasCriteria());
        $this->assertSame($criteria, $view->getCriteria());
        $this->assertNotSame($criteria, $view->getCriteriaCopy());
        $this->assertEquals($criteria, $view->getCriteriaCopy());
    }

    public function testWithoutCriteria()
    {
        $view = new TableView('name', 'Label', true);

        $this->assertSame('name', $view->getName());
        $this->assertSame('Label', $view->getLabel());
        $this->assertSame(true, $view->isDefault());
        $this->assertSame(false, $view->hasCriteria());
        $this->assertSame(null, $view->getCriteria());
        $this->assertSame(null, $view->getCriteriaCopy());
    }

    public function testCreateDefault()
    {
        $view = TableView::createDefault();

        $this->assertSame('default', $view->getName());
        $this->assertSame('Default', $view->getLabel());
        $this->assertSame(true, $view->isDefault());
        $this->assertSame(false, $view->hasCriteria());
        $this->assertSame(null, $view->getCriteria());
        $this->assertSame(null, $view->getCriteriaCopy());
    }
}