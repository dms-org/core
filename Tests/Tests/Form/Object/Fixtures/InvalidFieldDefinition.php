<?php

namespace Iddigital\Cms\Core\Tests\Form\Object\Fixtures;

use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\IndependentFormObject;

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