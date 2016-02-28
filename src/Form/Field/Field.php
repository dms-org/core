<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Processor\IFieldProcessorDependentOnInitialValue;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IFieldType;
use Dms\Core\Form\InvalidInputException;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;
use Dms\Core\Util\Debug;

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
    private $customProcessors;

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
    public function __construct(string $name, string $label, IFieldType $type, array $processors, $initialValue = null)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'processors', $processors, IFieldProcessor::class);
        InvalidArgumentException::verifyNotNull(__METHOD__, 'name', $name);
        InvalidArgumentException::verifyNotNull(__METHOD__, 'label', $label);

        $this->name             = $name;
        $this->label            = $label;
        $this->customProcessors = $processors;
        $this->type             = $type;

        $this->processedType = $processors
            ? end($processors)->getProcessedType()
            : $type->getProcessedPhpType();

        if ($this->type->get(FieldType::ATTR_REQUIRED) || $this->type->has(FieldType::ATTR_DEFAULT)) {
            $this->processedType = $this->processedType->nonNullable();
        }

        $this->setInitialValue($initialValue);
    }

    private function validateInitialValue($initialValue)
    {
        $processedPhpType = $this->getProcessedType();

        if (!$processedPhpType->isOfType($initialValue)) {
            throw InvalidArgumentException::format(
                'Invalid initial value for form field \'%s\': expecting type of %s, %s given',
                $this->name, $processedPhpType->asTypeString(), Debug::getType($initialValue)
            );
        }
    }

    private function setInitialValue($initialValue)
    {
        if ($initialValue === null) {
            $this->initialValue = null;
            $this->type = $this->type->with(FieldType::ATTR_INITIAL_VALUE, null);
            return;
        }

        $this->validateInitialValue($initialValue);
        $this->initialValue = $initialValue;

        $unprocessedInitialValue = $initialValue;
        foreach (array_reverse($this->customProcessors) as $processor) {
            /** @var IFieldProcessor $processor */
            $unprocessedInitialValue = $processor->unprocess($unprocessedInitialValue);
        }

        $this->type = $this->type->with(FieldType::ATTR_INITIAL_VALUE, $unprocessedInitialValue);
    }

    /**
     * Gets the field name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * {@inheritDoc}
     */
    public function getType() : IFieldType
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getProcessors() : array
    {
        return array_merge($this->type->getProcessors(), $this->customProcessors);
    }

    /**
     * {@inheritDoc}
     */
    public function getProcessedType() : IType
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
     * @inheritDoc
     */
    public function getUnprocessedInitialValue()
    {
        $initialValue = $this->getInitialValue();

        return $initialValue === null ? null : $this->unprocess($initialValue);
    }

    /**
     * {@inheritDoc}
     */
    public function process($input)
    {
        $oldInput = $input;
        /** @var Message[] $messages */
        $messages = [];

        foreach ($this->getProcessors() as $processor) {
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
        foreach (array_reverse($this->getProcessors()) as $processor) {
            $input = $processor->unprocess($input);
        }

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function withName(string $name, string $label = null) : IField
    {
        $clone        = clone $this;
        $clone->name  = $name;
        $clone->label = $label ?: $clone->label;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withInitialValue($value) : IField
    {
        $clone = clone $this;
        $clone->setInitialValue($value);

        foreach ($clone->customProcessors as $key => $processor) {
            if ($processor instanceof IFieldProcessorDependentOnInitialValue) {
                $clone->customProcessors[$key] = $processor->withInitialValue($value);
            }
        }

        return $clone;
    }
}