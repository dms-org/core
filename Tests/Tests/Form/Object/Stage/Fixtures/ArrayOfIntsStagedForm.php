<?php

namespace Dms\Core\Tests\Form\Object\Stage\Fixtures;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\Object\Stage\StagedFormObject;
use Dms\Core\Form\Object\Stage\StagedFormObjectDefinition;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfIntsStagedForm extends StagedFormObject
{
    /**
     * @var int
     */
    public $length;

    /**
     * @var int[]
     */
    public $ints;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function defineClass(ClassDefinition $class)
    {
        $class->property($this->length)->asInt();
        $class->property($this->ints)->asArrayOf(Type::int());
    }

    /**
     * Defines the staged form.
     *
     * @param StagedFormObjectDefinition $form
     */
    protected function defineForm(StagedFormObjectDefinition $form)
    {
        $form->stage(function (FormObjectDefinition $form) {
            $form->section('Length', [
                    $form->field($this->length)
                            ->name('length')
                            ->label('Length')
                            ->int()
                            ->required()
                            ->greaterThan(0)
            ]);
        });

        $form->stageDependentOn(['length'], function (FormObjectDefinition $form) {
            $form->section('Numbers', [
                    $form->field($this->ints)
                            ->name('ints')
                            ->label('Numbers')
                            ->arrayOf(Field::element()->int()->required())
                            ->required()
                            ->exactLength($this->length)
            ]);
        });
    }
}