<?php

namespace Dms\Core\Common\Crud\Action\Object\Mapping;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Dms\Core\Form\Builder\StagedForm;
use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Module\IStagedFormDtoMapping;

/**
 * The wrapper object action form mapping that wraps another form
 * dto mapping for the data dto.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WrapperObjectActionFormMapping extends ObjectActionFormMapping
{
    /**
     * @var IStagedForm|null
     */
    private $objectForm;

    /**
     * @var array|null
     */
    private $objectData;

    /**
     * @var IStagedFormDtoMapping|null
     */
    private $dataFormMapping;

    /**
     * @inheritDoc
     */
    public function __construct(IForm $objectForm, IStagedFormDtoMapping $dataFormMapping = null)
    {
        if ($dataFormMapping) {
            $stagedForm  = StagedForm::begin($objectForm)
                    ->embed($dataFormMapping->getStagedForm())
                    ->build();
            $dataDtoType = $dataFormMapping->getDtoType();
        } else {
            $stagedForm  = $objectForm->asStagedForm();
            $dataDtoType = null;
        }

        parent::__construct($stagedForm, $dataDtoType);
        $this->dataFormMapping = $dataFormMapping;
        $this->objectForm      = $objectForm;
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
        $objectData = $this->objectData
                ? $this->objectData
                : $this->objectForm->process($submission);

        $dataDto = $this->dataFormMapping
                ? $this->dataFormMapping->mapFormSubmissionToDto($submission)
                : null;

        return new ObjectActionParameter(
                $objectData[IObjectAction::OBJECT_FIELD_NAME],
                $dataDto
        );
    }

    /**
     * @inheritDoc
     */
    public function withSubmittedFirstStage(array $firstStageSubmission)
    {
        /** @var self $clone */
        $clone = clone $this;

        if ($clone->objectData) {
            $clone->dataFormMapping = $clone->dataFormMapping->withSubmittedFirstStage($firstStageSubmission);
            $clone->stagedForm = $clone->dataFormMapping->getStagedForm();
        } else {
            $clone->stagedForm = $clone->stagedForm->withSubmittedFirstStage($firstStageSubmission);
            $clone->objectData = $firstStageSubmission;
        }

        return $clone;
    }


}