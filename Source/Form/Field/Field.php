<?php

namespace Iddigital\Cms\Core\Form\Field;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Form\InvalidInputException;
use Iddigital\Cms\Core\Form\IFieldType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

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
     * @param string            $name
     * @param string            $label
     * @param IFieldType             $type
     * @param IFieldProcessor[] $processors
     */
    public function __construct($name, $label, IFieldType $type, array $processors)
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
     * {@inheritdoc}
     */
    public function withName($name)
    {
        $clone       = clone $this;
        $clone->name = $name;

        return $clone;
    }
}