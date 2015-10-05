<?php

namespace Iddigital\Cms\Core\Form\Object\Stage;

use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Util\Reflection;

/**
 * The staged form object definition class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedFormObjectDefinition
{
    /**
     * @var FinalizedClassDefinition
     */
    private $finalizedClass;

    /**
     * @var callable[]
     */
    private $stageCallbacks = [];

    /**
     * StagedFormObjectDefinition constructor.
     *
     * @param FinalizedClassDefinition $finalizedClass
     */
    public function __construct(FinalizedClassDefinition $finalizedClass)
    {
        $this->finalizedClass = $finalizedClass;
    }

    /**
     * Defines a stage in the form object.
     *
     * Example:
     * <code>
     * ->stage(function (FormObjectDefinition $form) {
     *      $form->section('Section Title', [
     *              $form->field($this->field)->name('field')->label('Field')->string(),
     *      ]);
     * });
     * </code>
     *
     * @param callable $defineStageCallback
     *
     * @return void
     */
    public function stage(callable $defineStageCallback)
    {
        $this->stageCallbacks[] = function (StagedFormObject $instance) use ($defineStageCallback) {
            $defineStageCallback    = \Closure::bind(
                    Reflection::fromCallable($defineStageCallback)->getClosure(),
                    $instance
            );

            $form = new FormObjectDefinition(
                    new ClassDefinition(
                            $instance,
                            new \ReflectionClass(get_class($instance)),
                            StagedFormObject::class
                    )
            );
            $defineStageCallback($form);

            return $form->finalize($this->finalizedClass);
        };
    }


    /**
     * @param StagedFormObject $instance
     *
     * @return FinalizedStagedFormObjectDefinition
     */
    public function finalize(StagedFormObject $instance)
    {
        return new FinalizedStagedFormObjectDefinition($instance, $this->stageCallbacks);
    }
}