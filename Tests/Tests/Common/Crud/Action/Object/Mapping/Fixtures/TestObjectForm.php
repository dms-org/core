<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures;

use Iddigital\Cms\Core\Common\Crud\Form\ObjectStagedFormObject;
use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObjectDefinition;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestObjectForm extends ObjectStagedFormObject
{
    /**
     * @var string
     */
    public $string;

    /**
     * @inheritDoc
     */
    public function getObjectType()
    {
        return TestEntity::class;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     *
     * @return void
     */
    protected function defineClassStructure(ClassDefinition $class)
    {
        $class->property($this->string)->asString();
    }

    /**
     * Defines the following form stages.
     *
     * @param StagedFormObjectDefinition $form
     *
     * @return void
     */
    protected function defineFormStages(StagedFormObjectDefinition $form)
    {
        $form->stage(function (FormObjectDefinition $form) {
            $form->section('Data', [
                    $form->field($this->string)->name('string')->label('String')->string()->required(),
            ]);
        });
    }
}