<?php

namespace Dms\Core\Tests\Common\Crud\Form\Fixtures;

use Dms\Core\Form\Field\Builder\Field;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestObjectForm extends ObjectForm
{
    public function define(ObjectFormDefinition $form)
    {
        $form->bindTo(SomeObject::class);

        $form->section('Details', [
            $form->field(
                Field::create()->name('name')->label('Name')->string()
            )->bindToProperty(SomeObject::NAME),
            //
            $form->field(
                Field::create()->name('age')->label('Age')->string()
            )->bindToProperty(SomeObject::AGE),
        ]);
    }
}