<?php

namespace Dms\Core\Form\Builder;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\ConflictingFieldNameException;
use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\Form as ActualForm;
use Dms\Core\Form\FormSection;
use Dms\Core\Form\IField;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IFormProcessor;
use Dms\Core\Form\IFormSection;
use Dms\Core\Form\Processor\CustomFormProcessor;
use Dms\Core\Form\Processor\FormValidator;
use Dms\Core\Form\Processor\Validator\CustomFormValidator;
use Dms\Core\Form\Processor\Validator\MatchingFieldsValidator;

/**
 * The form builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Form
{
    /**
     * @var IField[]
     */
    private $fields = [];

    /**
     * @var IFormSection[]
     */
    private $sections = [];

    /**
     * @var IFormProcessor[]
     */
    private $processors = [];

    public function __construct(Form $previous = null)
    {
        if ($previous) {
            $this->fields        = $previous->fields;
            $this->sections      = $previous->sections;
            $this->processors    = $previous->processors;
        }
    }

    /**
     * @return static
     */
    public static function create()
    {
        $self = new static();

        return $self;
    }

    /**
     * @return IForm
     */
    final public function build()
    {
        return new ActualForm($this->sections, $this->processors);
    }

    /**
     * @param string                      $title
     * @param FieldBuilderBase[]|IField[] $fields
     *
     * @return static
     * @throws ConflictingFieldNameException
     */
    public function section($title, array $fields)
    {
        $fields = $this->buildAllFields($fields);

        foreach ($fields as $field) {
            $fieldName = $field->getName();

            if (isset($this->fields[$fieldName])) {
                throw new ConflictingFieldNameException($fieldName);
            } else {
                $this->fields[$fieldName] = $field;
            }
        }

        $this->sections[] = new FormSection($title, $fields);

        return $this;
    }

    /**
     * Adds an existing form section to the form.
     *
     * @param IFormSection $section
     *
     * @return static
     */
    public function addSection(IFormSection $section)
    {
        return $this->section($section->getTitle(), $section->getFields());
    }

    /**
     * Embeds the form sections inside the current form.
     *
     * @param IForm $embeddedForm
     *
     * @return static
     */
    public function embed(IForm $embeddedForm)
    {
        foreach ($embeddedForm->getSections() as $section) {
            $this->addSection($section);
        }
        
        foreach ($embeddedForm->getProcessors() as $processor) {
            $this->process($processor);
        }

        return $this;
    }

    /**
     * @param FormValidator $validator
     *
     * @return static
     */
    public function validate(FormValidator $validator)
    {
        $this->processors[] = $validator;

        return $this;
    }

    /**
     * @param IFormProcessor $processor
     *
     * @return static
     */
    public function process(IFormProcessor $processor)
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * Validates the supplied fields match.
     *
     * @param string $fieldName
     * @param string $otherFieldName
     *
     * @return static
     * @throws InvalidArgumentException If the fields dont exist
     */
    public function fieldsMatch($fieldName, $otherFieldName)
    {
        foreach ([$fieldName, $otherFieldName] as $name) {
            if (!isset($this->fields[$fieldName])) {
                throw InvalidArgumentException::format(
                        "Invalid call to %s: field with name %s is not defined", __METHOD__, $name);
            }
        }

        return $this->validate(new MatchingFieldsValidator($this->fields[$fieldName], $this->fields[$otherFieldName]));
    }

    /**
     * Validates the input according to the supplied callback.
     *
     * @param callable    $validation
     * @param string|null $messageId
     * @param array       $parameters
     *
     * @return static
     */
    public function assert(callable $validation, $messageId = null, array $parameters = [])
    {
        return $this->validate(new CustomFormValidator($validation, $messageId, $parameters));
    }

    /**
     * Maps the inputted value according to the supplied callback.
     *
     * @param callable $mapper
     * @param callable $reverseMapper
     *
     * @return static
     */
    public function map(callable $mapper, callable $reverseMapper)
    {
        return $this->process(new CustomFormProcessor($mapper, $reverseMapper));
    }

    /**
     * @param IField[]|FieldBuilderBase[] $fields
     *
     * @return IField[]
     */
    protected function buildAllFields(array $fields)
    {
        foreach ($fields as $key => $field) {
            if ($field instanceof FieldBuilderBase) {
                $fields[$key] = $field->build();
            }
        }

        return $fields;
    }
}