<?php

namespace Dms\Core\Tests\Package;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Package\Dashboard;
use Dms\Core\Package\IDashboardWidget;
use Dms\Core\Widget\IWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DashboardTest extends CmsTestCase
{
    public function testNew()
    {
        $dashboard = new Dashboard([
            $widget = $this->getMockForAbstractClass(IDashboardWidget::class),
        ]);

        $this->assertSame([$widget], $dashboard->getWidgets());
    }

    public function testInvalidWidget()
    {
        $this->expectException(InvalidArgumentException::class);

        new Dashboard([
                $this
        ]);
    }
}