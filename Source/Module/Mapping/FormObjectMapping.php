<?php

namespace Iddigital\Cms\Core\Module\Mapping;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Form\Object\FormObject;

/**
 * A form dto mapping that makes use of form objects
 * which define a form and a dto.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class FormObjectMapping extends StagedFormDtoMapping
{
    /**
     * @var FormObject
     */
    private $stagedForm;

    public function __construct(FormObject $formObject)
    {
        parent::__construct(
                $formObject->getForm()->asStagedForm(),
                $formObject->getFormDefinition()->getClass()->getClassName()
        );
        $this->stagedForm = $formObject;
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormSubmissionToDto(array $submission)
    {
        return $this->stagedForm->submitNew($submission);
    }
}