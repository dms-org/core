<?php

namespace Dms\Core\Tests\Widget;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\Permission;
use Dms\Core\Form\IForm;
use Dms\Core\Widget\FormDataWidget;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormDataWidgetTest extends CmsTestCase
{
    public function testNew()
    {
        $form = $this->getMockForAbstractClass(IForm::class);

        $authSystem          = $this->getMockForAbstractClass(IAuthSystem::class);
        $requiredPermissions = ['abc' => Permission::named('abc')];

        $widget = new FormDataWidget('form-widget', 'Data', $authSystem, $requiredPermissions, $form);

        $this->assertSame('form-widget', $widget->getName());
        $this->assertSame('Data', $widget->getLabel());
        $this->assertSame($form, $widget->getForm());

        $authSystem->expects(self::once())
            ->method('isAuthorized')
            ->with($requiredPermissions)
            ->willReturnOnConsecutiveCalls(true);

        $this->assertSame(true, $widget->isAuthorized());
    }
}