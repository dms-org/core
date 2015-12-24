<?php

namespace Dms\Core\Module\Mapping;

use Dms\Core\Form;
use Dms\Core\Form\Object\Stage\StagedFormObject;

/**
 * The staged form dto mapping that makes use of form objects
 * which define a staged form and the class itself the dto.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class StagedFormObjectMapping extends StagedFormDtoMapping
{
    /**
     * @var StagedFormObject
     */
    private $stagedFormObject;

    public function __construct(StagedFormObject $stagedFormObject)
    {
        parent::__construct(
                $stagedFormObject,
                get_class($stagedFormObject)
        );
        $this->stagedFormObject = $stagedFormObject;
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormSubmissionToDto(array $submission)
    {
        return $this->stagedFormObject->submitNew($submission);
    }

    /**
     * {@inheritdoc}
     */
    public function withSubmittedFirstStage(array $firstStageSubmission)
    {
        $clone = parent::withSubmittedFirstStage($firstStageSubmission);

        $clone->stagedFormObject = $clone->getStagedForm();

        return $clone;
    }
}