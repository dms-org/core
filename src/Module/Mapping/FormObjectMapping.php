<?php declare(strict_types = 1);

namespace Dms\Core\Module\Mapping;

use Dms\Core\Form;
use Dms\Core\Form\Object\FormObject;

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
    private $formObject;

    public function __construct(FormObject $formObject)
    {
        parent::__construct(
                $formObject->getForm()->asStagedForm(),
                get_class($formObject)
        );
        $this->formObject = $formObject;
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormSubmissionToDto(array $submission)
    {
        return $this->formObject->submitNew($submission);
    }
}