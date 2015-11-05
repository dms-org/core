<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping;

use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectStagedFormObject;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Model\IDataTransferObject;

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
        $this->stagedFormObject->with

        return new ObjectActionParameter(
                $stagedFormObject->getObject(),
                $stagedFormObject
        );
    }
}