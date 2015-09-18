<?php

namespace Iddigital\Cms\Core\Form\Stage;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\IFormStage;

/**
 * The form stage base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FormStage implements IFormStage
{
    /**
     * @inheritDoc
     */
    final public function loadForm(array $previousSubmission = null)
    {
        if ($this->requiresPreviousSubmission() && $previousSubmission === null) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: form stage previous requires submission data and none provided',
                    __METHOD__
            );
        }

        $form = $this->getForm($previousSubmission);

        if ($form instanceof Form) {
            $form = $form->build();
        }

        return $form;
    }

    /**
     * @param array|null $previousSubmission
     *
     * @return IForm|Form
     */
    abstract protected function getForm(array $previousSubmission = null);
}