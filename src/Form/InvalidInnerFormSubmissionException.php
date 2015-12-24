<?php

namespace Dms\Core\Form;

/**
 * Exception for an invalid inner form submission.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class InvalidInnerFormSubmissionException extends InvalidFormSubmissionException
{
    /**
     * @var IField
     */
    private $field;

    /**
     * @inheritDoc
     */
    public function __construct(
            IField $field,
            InvalidFormSubmissionException $inner
    ) {
        parent::__construct(
                $inner->getForm(),
                $inner->getInput(),
                $inner->getInvalidInputExceptions(),
                $inner->getInvalidInnerFormSubmissionExceptions(),
                $inner->getUnmetConstraintExceptions()
        );

        $this->field = $field;
    }


    /**
     * @return IField
     */
    public function getField()
    {
        return $this->field;
    }
}
