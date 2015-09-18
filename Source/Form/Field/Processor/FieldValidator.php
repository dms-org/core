<?php

namespace Iddigital\Cms\Core\Form\Field\Processor;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The base field validator
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldValidator implements IFieldProcessor
{
    /**
     * @var IType
     */
    protected $inputType;

    /**
     * FieldProcessor constructor.
     *
     * @param IType $inputType
     */
    public function __construct(IType $inputType)
    {
        $this->inputType = $inputType;
        $this->validateInputType($this->inputType);
    }

    protected function validateInputType(IType $type)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function getProcessedType()
    {
        return $this->inputType;
    }

    /**
     * {@inheritDoc}
     */
    public function process($input, array &$messages)
    {
        if (!$this->inputType->isOfType($input)) {
            throw TypeMismatchException::argument(__METHOD__, 'input', $this->inputType->asTypeString(), $input);
        }

        if ($input === null) {
            return $input;
        }

        $this->validate($input, $messages);

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function unprocess($input)
    {
        return $input;
    }

    /**
     * Validates the supplied input and adds an
     * error messages to the supplied array.
     *
     * @param mixed     $input
     * @param Message[] $messages
     */
    abstract protected function validate($input, array &$messages);
}