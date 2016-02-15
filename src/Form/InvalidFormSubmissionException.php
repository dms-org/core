<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Exception\BaseException;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Language\Message;

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
    public function getForm() : IForm
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getInput() : array
    {
        return $this->input;
    }

    /**
     * @return InvalidInputException[]
     */
    public function getInvalidInputExceptions() : array
    {
        return $this->invalidInputExceptions;
    }

    /**
     * @param string $fieldName
     *
     * @return InvalidInputException|null
     */
    public function getInvalidInputExceptionFor(string $fieldName)
    {
        return isset($this->invalidInputExceptions[$fieldName])
                ? $this->invalidInputExceptions[$fieldName]
                : null;
    }

    /**
     * @return InvalidInnerFormSubmissionException[]
     */
    public function getInvalidInnerFormSubmissionExceptions() : array
    {
        return $this->invalidInnerFormSubmissionExceptions;
    }

    /**
     * @param string $fieldName
     *
     * @return InvalidInnerFormSubmissionException|null
     */
    public function getInnerFormSubmissionExceptionFor(string $fieldName)
    {
        return isset($this->invalidInnerFormSubmissionExceptions[$fieldName])
                ? $this->invalidInnerFormSubmissionExceptions[$fieldName]
                : null;
    }

    /**
     * @param string $fieldName
     *
     * @return Message[]
     */
    public function getMessagesFor(string $fieldName) : array
    {
        $invalidInputException = $this->getInvalidInputExceptionFor($fieldName);

        if ($invalidInputException) {
            return $invalidInputException->getMessages();
        }

        $invalidFormSubmission = $this->getInnerFormSubmissionExceptionFor($fieldName);

        if ($invalidFormSubmission) {
            return $invalidFormSubmission->getFieldMessageMap();
        }

        return [];
    }

    /**
     * @return Message[][]
     */
    public function getFieldMessageMap() : array
    {
        $messages = [];

        foreach ($this->form->getFields() as $field) {
            $messages[$field->getName()] = $this->getMessagesFor($field->getName());
        }

        return $messages;
    }

    /**
     * @return UnmetConstraintException[]
     */
    public function getUnmetConstraintExceptions() : array
    {
        return $this->unmetConstraintExceptions;
    }

    /**
     * @return Message[]
     */
    public function getAllConstraintMessages() : array
    {
        $messages = [];

        foreach ($this->unmetConstraintExceptions as $exception) {
            foreach ($exception->getMessages() as $message) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * @return Message[]
     */
    public function getAllMessages() : array
    {
        $messages = [];

        foreach ($this->invalidInputExceptions as $inputException) {
            $messages = array_merge($messages, $inputException->getMessages());
        }

        $messages = array_merge($messages, $this->getAllConstraintMessages());

        foreach ($this->invalidInnerFormSubmissionExceptions as $e) {
            $innerFormMessages = [];

            foreach ($e->getAllMessages() as $message) {
                $parameters = $message->getParameters();

                if (isset($parameters['field'])) {
                    $parameters['field'] = $e->getField()->getLabel() . ' > ' . $parameters['field'];
                }

                $innerFormMessages[] = $message->withParameters($parameters);
            }

            $messages = array_merge($messages, $innerFormMessages);
        }

        return $messages;
    }
}
