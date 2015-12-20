<?php

namespace Dms\Core\Form\Stage;

use Dms\Core\Form\IForm;

/**
 * The independent form stage base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IndependentFormStage extends FormStage
{
    /**
     * @var IForm
     */
    protected $form;

    /**
     * IndependentFormStage constructor.
     *
     * @param IForm $form
     */
    public function __construct(IForm $form)
    {
        $this->form = $form;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredFieldNames()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function getForm(array $previousSubmission = null)
    {
        return $this->form;
    }

    /**
     * @inheritDoc
     */
    public function getDefinedFieldNames()
    {
        return $this->form->getFieldNames();
    }
}