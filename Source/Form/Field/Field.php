<?php

namespace Iddigital\Cms\Core\Form\Field;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Form\IFieldType;
use Iddigital\Cms\Core\Form\InvalidInputException;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The field class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Field implements IField
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var IFieldType
     */
    private $type;

    /**
     * @var IFieldProcessor[]
     */
    private $processors;

    /**
     * @var IType
     */
    private $processedType;

    /**
     * @var mixed
     */
    private $initialValue;

    /**
     * @param string            $name
     * @param string            $label
     * @param IFieldType        $type
     * @param IFieldProcessor[] $processors
     * @param mixed             $initialValue
     *
     * @throws InvalidArgumentException
     */
    public function __construct($name, $label, IFieldType $type, array $processors, $initialValue = null)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'processors', $processors, IFieldProcessor::class);
        InvalidArgumentException::verifyNotNull(__METHOD__, 'name', $name);
        InvalidArgumentException::verifyNotNull(__METHOD__, 'label', $label);

        $this->name       = $name;
        $this->label      = $label;
        $this->processors = array_merge($type->getProcessors(), $processors);
        $this->type       = $type;

        $this->processedType = $this->processors
                ? end($this->processors)->getProcessedType()
                : $type->getPhpTypeOfInput();

        $this->setInitialValue($initialValue);
    }

    private function setInitialValue($initialValue)
    {
        if ($initialValue === null) {
            $this->initialValue = null;
        }

        $processedPhpType = $this->type->getProcessedPhpType();

        if (!$processedPhpType->isOfType($initialValue)) {
            throw InvalidArgumentException::format(
                    'Invalid initial value for form field \'%s\': expecting type of %s, %s given',
                    $this->name, $processedPhpType->asTypeString(), Debug::getType($this->initialValue)
            );
        }

        $this->initialValue = $this->unprocess($initialValue);
    }

    /**
     * Gets the field name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * {@inheritDoc}
     */
    public function getProcessedType()
    {
        return $this->processedType;
    }

    /**
     * {@inheritDoc}
     */
    public function getInitialValue()
    {
        return $this->initialValue;
    }

    /**
     * {@inheritDoc}
     */
    public function process($input)
    {
        $oldInput = $input;
        /** @var Message[] $messages */
        $messages = [];

        foreach ($this->processors as $processor) {
            $input = $processor->process($input, $messages);

            if (!empty($messages)) {
                foreach ($messages as $key => $message) {
                    $messages[$key] = $message->withParameters([
                            'field' => $this->label,
                            'input' => $oldInput,
                    ]);
                }

                throw new InvalidInputException($this, $messages);
            }
        }

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function unprocess($processedInput)
    {
        $input = $processedInput;

        /** @var IFieldProcessor $processor */
        foreach (array_reverse($this->processors) as $processor) {
            $input = $processor->unprocess($input);
        }

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function withName($name, $label = null)
    {
        $clone        = clone $this;
        $clone->name  = $name;
        $clone->label = $label ?: $clone->label;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withInitialValue($value)
    {
        $clone = clone $this;
        $clone->setInitialValue($value);

        return $clone;
    }


}