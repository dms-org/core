<?php

namespace Iddigital\Cms\Core\Tests\Form\Object\Fixtures;

use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\IndependentFormObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubFormWithDefaults extends IndependentFormObject
{
    /**
     * @var float
     */
    public $default = 0.123;

    /**
     * @var string
     */
    public $awesome;

    protected function defineForm(FormObjectDefinition $form)
    {
        $this->awesome = 'cool';

        $form->section('Sub', [
            //
            $form->field($this->default)
                    ->name('default')
                    ->label('Default')
                    ->decimal(),
            //
            $form->field($this->awesome)
                    ->name('awesome')
                    ->label('Awesome')
                    ->string(),
        ]);
    }
}