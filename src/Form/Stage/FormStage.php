<?php declare(strict_types = 1);

namespace Dms\Core\Form\Stage;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IFormStage;
use Dms\Core\Util\Debug;

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
    final public function requiresPreviousSubmission() : bool
    {
        return $this->areAllFieldsRequired() || count($this->getRequiredFieldNames()) > 0;
    }

    /**
     * @inheritDoc
     */
    public function areAllFieldsRequired() : bool
    {
        return $this->getRequiredFieldNames() === null;
    }

    /**
     * @inheritDoc
     */
    final public function loadForm(array $previousSubmission = null) : IForm
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

        /** @var IForm $form */
        foreach ($this->getDefinedFieldNames() as $fieldName) {
            if (!$form->hasField($fieldName)) {
                throw InvalidArgumentException::format(
                        'Invalid form loaded from form stage: form must have \'%s\' field defined, (%s) given',
                        $fieldName, Debug::formatValues($form->getFieldNames())
                );
            }
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