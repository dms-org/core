<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\Object\IndependentFormObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidFieldDefinition extends IndependentFormObject
{
    /**
     * @var string
     */
    public $data;

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     *
     * @return void
     */
    protected function defineForm(FormObjectDefinition $form)
    {
        $form->section('Page Content', [
            //
            $form->field($this->data)->name('data')->label('Data')->string()->required()->maxLength(50),
            //
            Field::name('invalid_field')->label('Invalid')->string(),
        ]);
    }
}