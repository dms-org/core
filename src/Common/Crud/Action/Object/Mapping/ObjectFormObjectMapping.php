<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Object\Mapping;

use Dms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Dms\Core\Common\Crud\Form\ObjectStagedFormObject;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Model\IDataTransferObject;

/**
 * The object action form mapping maps an instance of:
 *
 * @see    ObjectStagedFormObject
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectFormObjectMapping extends ObjectActionFormMapping
{
    /**
     * @var ObjectStagedFormObject
     */
    protected $stagedFormObject;

    /**
     * @inheritDoc
     */
    public function __construct(ObjectStagedFormObject $stagedFormObject)
    {
        parent::__construct($stagedFormObject, get_class($stagedFormObject));
        $this->stagedFormObject = $stagedFormObject;
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
        $stagedFormObject = $this->stagedFormObject->submitNew($submission);

        return new ObjectActionParameter(
                $stagedFormObject->getObject(),
                $stagedFormObject
        );
    }

    /**
     * @inheritDoc
     */
    public function withSubmittedFirstStage(array $firstStageSubmission)
    {
        $clone = parent::withSubmittedFirstStage($firstStageSubmission);

        $clone->stagedFormObject = $clone->getStagedForm();

        return $clone;
    }


}