<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;

/**
 * The array object action form mapping maps a single staged form
 * with an array.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayObjectActionFormMapping extends ObjectActionFormMapping
{
    /**
     * @inheritDoc
     */
    public function __construct(IStagedForm $stagedForm)
    {
        parent::__construct($stagedForm, ArrayDataObject::class);
    }

    /**
     * Gets the supplied form submission data mapped to a dto.
     *
     * @param array $submission
     *
     * @return IDataTransferObject
     * @throws InvalidFormSubmissionException
     */
    public function mapFormSubmissionToDto(array $submission)
    {
        $formData = $this->getStagedForm()->process($submission);

        return new ObjectActionParameter(
                $formData[IObjectAction::OBJECT_FIELD_NAME],
                new ArrayDataObject($formData)
        );
    }
}