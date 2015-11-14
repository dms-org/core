<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition\Form;

use Iddigital\Cms\Core\Form\IStagedForm;

/**
 * The finalized CRUD form definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedCrudFormDefinition
{
    /**
     * @var string
     */
    protected $mode;

    /**
     * @var IStagedForm
     */
    protected $stagedForm;

    /**
     * @var callable[]
     */
    protected $onSubmitCallbacks = [];

    /**
     * FinalizedCrudFormDefinition constructor.
     *
     * @param string      $mode
     * @param IStagedForm $stagedForm
     * @param \callable[] $onSubmitCallbacks
     */
    public function __construct($mode, IStagedForm $stagedForm, array $onSubmitCallbacks)
    {
        $this->mode              = $mode;
        $this->stagedForm        = $stagedForm;
        $this->onSubmitCallbacks = $onSubmitCallbacks;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return IStagedForm
     */
    public function getStagedForm()
    {
        return $this->stagedForm;
    }

    /**
     * @return callable[]
     */
    public function getOnSubmitCallbacks()
    {
        return $this->onSubmitCallbacks;
    }
}