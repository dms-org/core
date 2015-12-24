<?php

namespace Dms\Core\Tests\Common\Crud\Form;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Common\Crud\Form\FormWithBinding;
use Dms\Core\Form\Binding\Field\FieldPropertyBinding;
use Dms\Core\Form\Binding\Field\GetterSetterMethodBinding;
use Dms\Core\Form\Binding\IFormBinding;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Tests\Form\Binding\Fixtures\TestFormBoundClass;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormWithBindingTest extends CmsTestCase
{
    public function testNew()
    {
        $form = Form::create()
                ->section('Input', [
                        Field::name('string')->label('String')->string()->required(),
                        Field::name('int')->label('Int')->int()->required(),
                        Field::name('bool')->label('Bool')->bool()->required(),
                ])
                ->build();

        $formWithBinding = new FormWithBinding(
                $form->getSections(), [],
                TestFormBoundClass::class,
                $fieldBindings = [
                        new FieldPropertyBinding('string', TestFormBoundClass::definition(), 'string'),
                        new GetterSetterMethodBinding('int', TestFormBoundClass::class, 'getInt', 'setInt'),
                        new FieldPropertyBinding('bool', TestFormBoundClass::definition(), 'bool'),
                ]
        );

        $this->assertEquals($form->getSections(), $formWithBinding->getSections());
        $this->assertInstanceOf(IFormBinding::class, $formWithBinding->getBinding());
        $this->assertEquals($formWithBinding, $formWithBinding->getBinding()->getForm());
        $this->assertEquals(TestFormBoundClass::class, $formWithBinding->getBinding()->getObjectType());
        $this->assertEquals($fieldBindings, array_values($formWithBinding->getBinding()->getFieldBindings()));
    }
}