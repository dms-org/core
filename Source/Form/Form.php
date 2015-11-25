<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
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
     * @var array
     */
    private $initialValues = [];

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

        foreach ($this->fields as $name => $field) {
            $this->initialValues[$name] = $field->getInitialValue();
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
     * {@inheritdoc}
     */
    final public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    final public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * {@inheritdoc}
     */
    final public function hasField($fieldName)
    {
        return isset($this->fields[$fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    final public function getField($fieldName)
    {
        if (!isset($this->fields[$fieldName])) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: invalid field name, expecting one of (%s), \'%s\' given',
                    __METHOD__, Debug::formatValues($this->getFieldNames()), $fieldName
            );
        }

        return $this->fields[$fieldName];
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
    final public function getInitialValues()
    {
        return $this->initialValues;
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
    public function validateProcessedValues(array $processedSubmission)
    {
        $processedKeys = array_keys($processedSubmission);
        $fieldKeys     = array_keys($this->fields);

        sort($processedKeys);
        sort($fieldKeys);

        if ($processedKeys !== $fieldKeys) {
            throw InvalidArgumentException::format(
                    'Invalid processed submission: expecting keys (%s), (%s) given',
                    Debug::formatValues($fieldKeys), Debug::formatValues($processedKeys)
            );
        }

        foreach ($processedSubmission as $fieldName => $value) {
            $expectedType = $this->fields[$fieldName]->getProcessedType();

            if (!$expectedType->isOfType($value)) {
                throw InvalidArgumentException::format(
                        'Invalid processed submission: expecting value for field \'%s\' to be of type %s, %s given',
                        $fieldName, $expectedType->asTypeString(), Type::from($value)->asTypeString()
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function unprocess(array $processedSubmission)
    {
        $this->validateProcessedValues($processedSubmission);

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

    /**
     * @inheritDoc
     */
    public function withInitialValues(array $initialProcessedValues)
    {
        $newFields = [];

        foreach ($initialProcessedValues as $fieldName => $initialValue) {
            $field = $this->getField($fieldName);

            $newFields[$field->getName()] = $field->withInitialValue($initialValue);
        }

        $sections = [];

        foreach ($this->sections as $section) {
            $newFieldsInSection = [];

            foreach ($section->getFields() as $field) {
                $newFieldsInSection[] = isset($newFields[$field->getName()])
                        ? $newFields[$field->getName()]
                        : $field;
            }

            $sections[] = new FormSection($section->getTitle(), $newFieldsInSection);
        }

        return new Form($sections, $this->processors);
    }
}