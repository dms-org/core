<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\BaseException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Language\Message;

/**
 * Exception for an invalid form submission.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class InvalidFormSubmissionException extends BaseException
{
    /**
     * @var IForm
     */
    private $form;

    /**
     * @var array
     */
    private $input;

    /**
     * @var InvalidInputException[]
     */
    private $invalidInputExceptions = [];

    /**
     * @var InvalidInnerFormSubmissionException[]
     */
    private $invalidInnerFormSubmissionExceptions = [];

    /**
     * @var UnmetConstraintException[]
     */
    private $unmetConstraintExceptions = [];

    /**
     * @param IForm                                 $form
     * @param array                                 $input
     * @param InvalidInputException[]               $invalidInputExceptions
     * @param InvalidInnerFormSubmissionException[] $invalidInnerFormSubmissionExceptions
     * @param UnmetConstraintException[]            $unmetConstraintExceptions
     */
    public function __construct(
            IForm $form,
            array $input,
            array $invalidInputExceptions,
            array $invalidInnerFormSubmissionExceptions,
            array $unmetConstraintExceptions
    ) {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'invalidInputExceptions', $invalidInputExceptions,
                InvalidInputException::class);

        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'invalidInnerFormSubmissionExceptions',
                $invalidInnerFormSubmissionExceptions,
                InvalidInnerFormSubmissionException::class);

        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'unmetConstraintExceptions', $unmetConstraintExceptions,
                UnmetConstraintException::class);

        $messages = [];

        foreach ($invalidInputExceptions as $exception) {
            foreach ($exception->getMessages() as $message) {
                $messages[] = $exception->getField()->getName() . ' => ' . $message->getId();
            }
        }


        foreach ($invalidInnerFormSubmissionExceptions as $exception) {
            $messages[]  = $exception->getField()->getName() . ' => { ' . $exception->getMessage() . ' }';
        }

        foreach ($unmetConstraintExceptions as $exception) {
            foreach ($exception->getMessages() as $message) {
                $messages[] = $message->getId();
            }
        }

        parent::__construct(implode(', ', $messages));

        $this->form  = $form;
        $this->input = $input;

        foreach ($invalidInputExceptions as $exception) {
            $this->invalidInputExceptions[$exception->getField()->getName()] = $exception;
        }

        foreach ($invalidInnerFormSubmissionExceptions as $exception) {
            $this->invalidInnerFormSubmissionExceptions[$exception->getField()->getName()] = $exception;
        }

        $this->unmetConstraintExceptions = $unmetConstraintExceptions;
    }

    /**
     * @return IForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return InvalidInputException[]
     */
    public function getInvalidInputExceptions()
    {
        return $this->invalidInputExceptions;
    }

    /**
     * @param IField $field
     *
     * @return InvalidInputException|null
     */
    public function getInvalidInputExceptionFor(IField $field)
    {
        return isset($this->invalidInputExceptions[$field->getName()])
                ? $this->invalidInputExceptions[$field->getName()]
                : null;
    }

    /**
     * @return InvalidInnerFormSubmissionException[]
     */
    public function getInvalidInnerFormSubmissionExceptions()
    {
        return $this->invalidInnerFormSubmissionExceptions;
    }

    /**
     * @param IField $field
     *
     * @return InvalidInnerFormSubmissionException|null
     */
    public function getInnerFormSubmissionExceptionFor(IField $field)
    {
        return isset($this->invalidInnerFormSubmissionExceptions[$field->getName()])
                ? $this->invalidInnerFormSubmissionExceptions[$field->getName()]
                : null;
    }

    /**
     * @param IField $field
     *
     * @return Message[]
     */
    public function getMessagesFor(IField $field)
    {
        $invalidInputException = $this->getInvalidInputExceptionFor($field);

        if ($invalidInputException) {
            return $invalidInputException->getMessages();
        }

        $invalidFormSubmission = $this->getInnerFormSubmissionExceptionFor($field);

        if ($invalidFormSubmission) {
            return $invalidFormSubmission->getFieldMessageMap();
        }

        return [];
    }

    /**
     * @return Message[][]
     */
    public function getFieldMessageMap()
    {
        $messages = [];

        foreach ($this->form->getFields() as $field) {
            $messages[$field->getName()] = $this->getMessagesFor($field);
        }

        return $messages;
    }

    /**
     * @return UnmetConstraintException[]
     */
    public function getUnmetConstraintExceptions()
    {
        return $this->unmetConstraintExceptions;
    }

    /**
     * @return Message[]
     */
    public function getAllConstraintMessages()
    {
        $messages = [];

        foreach ($this->unmetConstraintExceptions as $exception) {
            foreach ($exception->getMessages() as $message) {
                $messages[] = $message;
            }
        }

        return $messages;
    }
}
