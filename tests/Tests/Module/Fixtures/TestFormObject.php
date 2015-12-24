<?php

namespace Dms\Core\Tests\Module\Fixtures;

use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\Object\IndependentFormObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestFormObject extends IndependentFormObject
{
    /**
     * @var string|null
     */
    public $data;

    /**
     * TestDto constructor.
     *
     * @param string|null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     *
     * @return void
     */
    protected function defineForm(FormObjectDefinition $form)
    {
        $form->section('Input', [
                $form->field($this->data)->name('data')->label('Data')->string()
        ]);
    }
}