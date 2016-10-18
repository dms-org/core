<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Stage\IndependentFormStage;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Util\Debug;

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

        $this->sections = [];

        foreach ($sections as $section) {
            if (!empty($this->sections) && $section->doesContinuePreviousSection()) {
                /** @var IFormSection $lastSection */
                $lastSection      = array_pop($this->sections);
                $this->sections[] = new FormSection(
                    $lastSection->getTitle(),
                    array_merge($lastSection->getFields(), $section->getFields())
                );
            } else {
                $this->sections[] = $section;
            }
        }

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
    final public function getSections() : array
    {
        return $this->sections;
    }

    /**
     * {@inheritDoc}
     */
    final public function getProcessors() : array
    {
        return $this->processors;
    }

    /**
     * {@inheritdoc}
     */
    final public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    final public function getFieldNames() : array
    {
        return array_keys($this->fields);
    }

    /**
     * {@inheritdoc}
     */
    final public function hasField(string $fieldName) : bool
    {
        return isset($this->fields[$fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    final public function getField(string $fieldName) : IField
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
    final public function asStagedForm() : IStagedForm
    {
        return new StagedForm(new IndependentFormStage($this), []);
    }

    /**
     * {@inheritDoc}
     */
    final public function getInitialValues() : array
    {
        return $this->initialValues;
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $submission) : array
    {
        $processed                   = [];
        $invalidInputExceptions      = [];
        $invalidSubmissionExceptions = [];
        $unmetConstraintExceptions   = [];

        foreach ($this->fields as $name => $field) {
            try {
                $processed[$name] = null;
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
                'Invalid processed submission: expecting keys (%s), (%s) given, (%s) missing, (%s) added',
                Debug::formatValues($fieldKeys), Debug::formatValues($processedKeys),
                Debug::formatValues(array_diff($fieldKeys, $processedKeys)),
                Debug::formatValues(array_diff($processedKeys, $fieldKeys))
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
    public function unprocess(array $processedSubmission) : array
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
    public function withInitialValues(array $initialProcessedValues) : IForm
    {
        $newFields = [];

        foreach ($initialProcessedValues as $fieldName => $initialValue) {
            $field = $this->getField($fieldName);

            $newFields[$field->getName()] = $field->withInitialValue($initialValue);
        }

        $newFields = $newFields + $this->getFields();

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

    /**
     * @inheritDoc
     */
    public function withFieldNames(array $fieldNameMap) : IForm
    {
        $newSections   = [];
        $newProcessors = [];

        foreach ($this->sections as $section) {
            $newFields = [];

            foreach ($section->getFields() as $field) {
                if (isset($fieldNameMap[$field->getName()])) {
                    $field = $field->withName($fieldNameMap[$field->getName()]);
                }

                $newFields[] = $field;
            }

            $newSections[] = new FormSection($section->getTitle(), $newFields);
        }

        foreach ($this->processors as $processor) {
            $newProcessors[] = $processor->withFieldNames($fieldNameMap);
        }

        return new Form($newSections, $newProcessors);
    }
}