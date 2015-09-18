<?php

namespace Iddigital\Cms\Core\Form\Object\Stage;

use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedFormObjectDream extends StagedFormObject
{
    /**
     * @var StageOne
     */
    public $stageOne;

    /**
     * @var string
     */
    public $var;

    public function defineClass(ClassDefinition $class)
    {
        $class->property($this->stageOne)->asObject(StageOne::class);
        $class->property($this->var)->asString();
    }

    public function defineForm(StagedFormObjectDefinition $form)
    {
        $form->stage(function (FormObjectDefinition $stage) {
            $stage->field($this->stageOne)
                    ->name('stage_one')
                    ->label('One')
                    ->form(new StageOne());
        });

        $form->stage(function (FormObjectDefinition $stage) {
            $stage->field($this->var)
                    ->name('var')
                    ->label('Var')
                    ->string();
        });
    }

}