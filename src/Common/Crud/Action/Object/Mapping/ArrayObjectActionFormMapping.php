<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Object\Mapping;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Model\Object\ArrayDataObject;

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
     * @return object
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