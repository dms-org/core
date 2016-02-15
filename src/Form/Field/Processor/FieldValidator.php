<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

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
     * @return IType
     */
    public function getInputType() : \Dms\Core\Model\Type\IType
    {
        return $this->inputType;
    }

    /**
     * {@inheritDoc}
     */
    public function getProcessedType() : \Dms\Core\Model\Type\IType
    {
        return $this->inputType;
    }

    /**
     * {@inheritDoc}
     */
    public function process($input, array &$messages)
    {
        if (!$this->inputType->isOfType($input)) {
            throw TypeMismatchException::argument(
                    get_class($this) . '::' . __FUNCTION__,
                    'input', $this->inputType->asTypeString(), $input
            );
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