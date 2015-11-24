<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures;

use Iddigital\Cms\Core\Common\Crud\Form\ObjectStagedFormObject;
use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObjectDefinition;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\PropertyTypeDefiner;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestObjectForm extends ObjectStagedFormObject
{
    /**
     * @var string
     */
    protected $string;

    /**
     * Defines the structure of this class.
     *
     * @param PropertyTypeDefiner $object
     * @param ClassDefinition     $class
     *
     * @return void
     */
    protected function defineClassStructure(PropertyTypeDefiner $object, ClassDefinition $class)
    {
        $object->asObject(TestEntity::class);

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
            $form->field($this->string)->name('string')->label('String')->string()->required();
        });
    }
}