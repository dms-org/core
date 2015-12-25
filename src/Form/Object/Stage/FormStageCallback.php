<?php

namespace Dms\Core\Form\Object\Stage;

use Dms\Core\Form\Object\FinalizedFormObjectDefinition;

/**
 * The form stage definition callback.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormStageCallback
{
    /**
     * @var callable
     */
    protected $defineFormStageCallback;

    /**
     * @var string[]|null
     */
    protected $fieldsDependentOn;

    /**
     * @var string[]
     */
    protected $fieldsDefinedWithinStage;

    /**
     * FormStageCallback constructor.
     *
     * @param callable      $defineFormStageCallback
     * @param null|string[] $fieldsDependentOn
     * @param string[]      $fieldsDefinedWithinStage
     */
    public function __construct(callable $defineFormStageCallback, array $fieldsDependentOn = null, array $fieldsDefinedWithinStage)
    {
        $this->defineFormStageCallback  = $defineFormStageCallback;
        $this->fieldsDependentOn        = $fieldsDependentOn;
        $this->fieldsDefinedWithinStage = $fieldsDefinedWithinStage;
    }

    /**
     * @param StagedFormObject $instance
     *
     * @return FinalizedFormObjectDefinition
     */
    public function defineFormStage(StagedFormObject $instance)
    {
        return call_user_func($this->defineFormStageCallback, $instance);
    }

    /**
     * @return null|string[]
     */
    public function getFieldsDependentOn()
    {
        return $this->fieldsDependentOn;
    }

    /**
     * @return string[]
     */
    public function getFieldsDefinedWithinStage()
    {
        return $this->fieldsDefinedWithinStage;
    }
}