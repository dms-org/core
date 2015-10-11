<?php

namespace Iddigital\Cms\Core\Module\Mapping;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObject;

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
}