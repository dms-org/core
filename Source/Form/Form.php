<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The form class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Form implements IForm
{
    /**
     * @var IField[]
     */
    private $fields = [];

    /**
     * @var IFormSection[]
     */
    private $sections;

    /**
     * @var IFormProcessor[]
     */
    private $processors;

    /**
     * @param IFormSection[]   $sections
     * @param IFormProcessor[] $processors
     *
     * @throws ConflictingFieldNameException
     */
    public function __construct(array $sections, array $processors)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'sections', $sections, IFormSection::class);
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'processors', $processors, IFormProcessor::class);

        $this->sections   = $sections;
        $this->processors = $processors;

        foreach ($sections as $section) {
            foreach ($section->getFields() as $field) {
                $fieldName = $field->getName();
                if (isset($this->fields[$fieldName])) {
                    throw new ConflictingFieldNameException($fieldName);
                }

                $this->fields[$fieldName] = $field;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function getSections()
    {
        return $this->sections;
    }

    /**
     * {@inheritDoc}
     */
    final public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * @return IForm[]
     */
    final public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    final public function getField($fieldName)
    {
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName] : null;
    }

    /**
     * {@inheritDoc}
     */
    final public function asStagedForm()
    {
        return new StagedForm(new IndependentFormStage($this), []);
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $submission)
    {
        $processed                   = [];
        $invalidInputExceptions      = [];
        $invalidSubmissionExceptions = [];
        $unmetConstraintExceptions   = [];

        foreach ($this->fields as $name => $field) {
            try {
                $processed[$name] = $field->process(isset($submission[$name]) ? $submission[$name] : null);
            } catch (InvalidInputException $e) {
                $invalidInputExceptions[] = $e;
            } catch (InvalidFormSubmissionException $e) {
                $invalidSubmissionExceptions[] = new InvalidInnerFormSubmissionException($field, $e);
            }
        }

        foreach ($this->processors as $processor) {
            /** @var Message[] $messages */
            $messages  = [];
            $processed = $processor->process($processed, $messages);

            if (!empty($messages)) {
                $unmetConstraintExceptions[] = new UnmetConstraintException($processor, $messages);
            }
        }

        if (empty($invalidInputExceptions) && empty($invalidSubmissionExceptions) && empty($unmetConstraintExceptions)) {
            return $processed;
        }

        throw new InvalidFormSubmissionException(
                $this,
                $submission,
                $invalidInputExceptions,
                $invalidSubmissionExceptions,
                $unmetConstraintExceptions
        );
    }

    /**
     * {@inheritDoc}
     */
    public function unprocess(array $processedSubmission)
    {
        $processedKeys = array_keys($processedSubmission);
        $fieldKeys = array_keys($this->fields);

        sort($processedKeys);
        sort($fieldKeys);

        if ($processedKeys !== $fieldKeys) {
            throw InvalidArgumentException::format(
                    'Invalid processed submission: expecting keys (%s), (%s) given',
                    Debug::formatValues($fieldKeys), Debug::formatValues($processedKeys)
            );
        }

        $submission = array_intersect_key($processedSubmission, $this->fields);

        /** @var IFormProcessor $processor */
        foreach (array_reverse($this->processors) as $processor) {
            $submission = $processor->unprocess($submission);
        }

        $unprocessedSubmission = [];

        foreach ($this->fields as $name => $field) {
            $unprocessedSubmission[$name] = $field->unprocess($submission[$name]);
        }

        return $unprocessedSubmission;
    }
}