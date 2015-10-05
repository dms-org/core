<?php

namespace Iddigital\Cms\Core\Tests\Module\Fixtures;

use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObject;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObjectDefinition;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestStagedFormObject extends StagedFormObject
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
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function defineClass(ClassDefinition $class)
    {
        $class->property($this->data)->nullable()->asString();
    }

    /**
     * Defines the staged form.
     *
     * @param StagedFormObjectDefinition $form
     */
    protected function defineForm(StagedFormObjectDefinition $form)
    {
        $form->stage(function (FormObjectDefinition $form) {
            $form->section('Input', [
                    $form->field($this->data)->name('data')->label('Data')->string()
            ]);
        });
    }
}